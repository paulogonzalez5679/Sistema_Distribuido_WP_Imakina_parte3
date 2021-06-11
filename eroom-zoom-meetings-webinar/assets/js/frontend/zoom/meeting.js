window.addEventListener('DOMContentLoaded', function(event) {
  console.log('DOM fully loaded and parsed');
  websdkready();
});

function websdkready() {
  var testTool = window.testTool;
  var meetingConfig = {
    apiKey: API_KEY,
    meetingNumber: meeting_id,
    userName: username,
    passWord: meeting_password,
    leaveUrl: leaveUrl,
    role: 0, //0-Attendee,1-Host,5-Assistant
    userEmail: email,
    lang: lang,
    signature: "",
    china: 0,//0-GLOBAL, 1-China
  };

  // a tool use debug mobile device
  if (testTool.isMobileDevice()) {
    vConsole = new VConsole();
  }
  // it's option if you want to change the WebSDK dependency link resources. setZoomJSLib must be run at first
  // ZoomMtg.setZoomJSLib("https://source.zoom.us/1.8.5/lib", "/av"); // CDN version defaul
  // if (meetingConfig.china)
  //  ZoomMtg.setZoomJSLib("https://jssdk.zoomus.cn/1.8.5/lib", "/av"); // china cdn option

  ZoomMtg.preLoadWasm();
  ZoomMtg.prepareJssdk();


  ZoomMtg.inMeetingServiceListener('onUserJoin', function (data) {
    console.log('inMeetingServiceListener onUserJoin', data);
  });

  ZoomMtg.inMeetingServiceListener('onUserLeave', function (data) {
    console.log('inMeetingServiceListener onUserLeave', data);
  });

  ZoomMtg.inMeetingServiceListener('onUserIsInWaitingRoom', function (data) {
    console.log('inMeetingServiceListener onUserIsInWaitingRoom', data);
  });

  ZoomMtg.inMeetingServiceListener('onMeetingStatus', function (data) {
    console.log('inMeetingServiceListener onMeetingStatus', data);
  });

  var raw = JSON.stringify({
    "api_key":meetingConfig.apiKey,
    "meetingNumber":meetingConfig.meetingNumber,
    "role":meetingConfig.role
  });

  fetch(`${endpoint}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: raw,
  }).then(result => result.text())
      .then(response => {
        var signatureres = JSON.parse(response);
        var signature = signatureres[0];
        beginJoin(signature);
      })
  function beginJoin(signature) {
    ZoomMtg.init({
      leaveUrl: meetingConfig.leaveUrl,
      webEndpoint: meetingConfig.webEndpoint,
      success: function () {
        ZoomMtg.i18n.load(meetingConfig.lang);
        ZoomMtg.i18n.reload(meetingConfig.lang);
        ZoomMtg.join({
          meetingNumber: meetingConfig.meetingNumber,
          userName: meetingConfig.userName,
          signature: signature,
          apiKey: meetingConfig.apiKey,
          userEmail: meetingConfig.userEmail,
          passWord: meetingConfig.passWord,
          success: function (res) {
            console.log("join meeting success");
            ZoomMtg.getAttendeeslist({});
            ZoomMtg.getCurrentUser({
              success: function (res) {
                console.log("success getCurrentUser", res.result.currentUser);
              },
            });
          },
          error: function (res) {
            console.log(res);
          },
        });
      },
      error: function (res) {
        console.log(res);
      },
    });
  }

};
