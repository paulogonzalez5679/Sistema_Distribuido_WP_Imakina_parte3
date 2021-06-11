(function(){

	console.log('checkSystemRequirements');
	console.log(JSON.stringify(ZoomMtg.checkSystemRequirements()));

    // it's option if you want to change the WebSDK dependency link resources. setZoomJSLib must be run at first
    // if (!china) ZoomMtg.setZoomJSLib('https://source.zoom.us/1.7.7/lib', '/av'); // CDN version default
    // else ZoomMtg.setZoomJSLib('https://jssdk.zoomus.cn/1.7.7/lib', '/av'); // china cdn option 
    // ZoomMtg.setZoomJSLib('http://localhost:9999/node_modules/@zoomus/websdk/dist/lib', '/av'); // Local version default, Angular Project change to use cdn version
    ZoomMtg.preLoadWasm();
    ZoomMtg.prepareJssdk();
    

    /**
     * NEVER PUT YOUR ACTUAL API SECRET IN CLIENT SIDE CODE, THIS IS JUST FOR QUICK PROTOTYPING
     * The below generateSignature should be done server side as not to expose your api secret in public
     * You can find an eaxmple in here: https://marketplace.zoom.us/docs/sdk/native-sdks/web/essential/signature
     */

    testTool = window.testTool;

    if (testTool.getCookie("meeting_lang")) document.getElementById('meeting_lang').value = testTool.getCookie("meeting_lang");
   
    document.getElementById('meeting_lang').addEventListener('change', function(e){
        testTool.setCookie("meeting_lang", document.getElementById('meeting_lang').value);
        $.i18n.reload(document.getElementById('meeting_lang').value);
    });
    
    document.getElementById('clear_all').addEventListener('click', function(e) {
        testTool.deleteAllCookies();
        document.getElementById('display_name').value = '';
        document.getElementById('meeting_number').value = '';
        document.getElementById('meeting_pwd').value = '';
        document.getElementById('meeting_lang').value = 'en-US';
        document.getElementById('meeting_role').value = 0;
    });

    document.getElementById('join_meeting').addEventListener('click', function(e){

        e.preventDefault();

        if(!this.form.checkValidity()){
            alert("Enter Name and Meeting Number");
            return false;
        }

        var meetConfig = {
            apiKey: API_KEY,
            meetingNumber: parseInt(document.getElementById('meeting_number').value),
            userName: document.getElementById('display_name').value,
            userEmail: (document.getElementById('email')) ? document.getElementById('email').value : '',
            passWord: document.getElementById('meeting_pwd').value,
            leaveUrl: leaveUrl,
            role: (document.getElementById('email')) ? 0 : parseInt(document.getElementById('meeting_role').value, 10)
        };
        testTool.setCookie("meeting_number", meetConfig.meetingNumber);
        testTool.setCookie("meeting_pwd", meetConfig.passWord);
              var raw = JSON.stringify({
                  "api_key":meetConfig.apiKey,
                  "meetingNumber":meetConfig.meetingNumber,
                  "role":meetConfig.role
              });

              fetch(`${endpoint}`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                  },
                  body: raw,
              })
                  .then(result => result.text())
                  .then(response => {

                      var signatureres = JSON.parse(response);
                      var signature    = signatureres[0];

                      ZoomMtg.init({
                          leaveUrl: 'http://www.zoom.us',
                          isSupportAV: true,
                          success: function () {
                              ZoomMtg.join(
                                  {
                                      meetingNumber: meetConfig.meetingNumber,
                                      userName: meetConfig.userName,
                                      userEmail: meetConfig.userEmail,
                                      signature: signature,
                                      apiKey: meetConfig.apiKey,
                                      passWord: meetConfig.passWord,
                                      success: function(res){
                                          $('#nav-tool').hide();
                                          console.log('join meeting success');
                                      },
                                      error: function(res) {
                                          console.log(res);
                                      }
                                  }
                              );
                          },

                          error: function(res) {
                              console.log(res);
                          }
                      });

                  })

    });

})();
