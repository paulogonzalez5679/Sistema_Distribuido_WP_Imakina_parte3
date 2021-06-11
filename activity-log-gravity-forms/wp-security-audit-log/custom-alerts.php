<?php

$custom_alerts = array(
	__( 'Gravity Forms', 'wsal-gravity-forms' ) => array(
		__( 'Monitor Gravity Forms', 'wsal-gravity-forms' ) => array(

			array(
				5700,
				WSAL_LOW,
				__( 'A form was created, modified', 'wsal-gravity-forms' ),
				__( 'Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EditorLinkForm%', 'wsal-gravity-forms' ),
				'gravityforms_forms',
				'created',
			),

			array(
				5701,
				WSAL_MEDIUM,
				__( 'A form was moved to trash', 'wsal-gravity-forms' ),
				__( 'Moved the form to trash %LineBreak% Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EditorLinkForm%', 'wsal-gravity-forms' ),
				'gravityforms_forms',
				'created',
			),

			array(
				5702,
				WSAL_MEDIUM,
				__( 'A form was permanently deleted', 'wsal-gravity-forms' ),
				__( 'Permanently deleted the form %form_name% %LineBreak% Form ID: %form_id%', 'wsal-gravity-forms' ),
				'gravityforms_forms',
				'created',
			),

			array(
				5703,
				WSAL_MEDIUM,
				__( 'A form setting was modified', 'wsal-gravity-forms' ),
				__( 'The setting %setting_name% in form %form_name% %LineBreak% Previous value: %old_setting_value% %LineBreak% New value: %setting_value% %LineBreak% Form ID: %form_id% %LineBreak% %EditorLinkForm%', 'wsal-gravity-forms' ),
				'gravityforms_forms',
				'modified',
			),

			array(
				5704,
				WSAL_LOW,
				__( 'A form was duplicated', 'wsal-gravity-forms' ),
				__( 'Source form: %original_form_name% %LineBreak% New form name: %new_form_name% %LineBreak% Source form ID: %original_form_id% %LineBreak% New form ID: %new_form_id% %LineBreak% %EditorLinkFormDuplicated%', 'wsal-gravity-forms' ),
				'gravityforms_forms',
				'duplicated',
			),

			array(
				5715,
				WSAL_MEDIUM,
				__( 'A field was created, modified or deleted', 'wsal-gravity-forms' ),
				__( 'Field name: %field_name% %LineBreak% Field type: %field_type% %LineBreak% Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EditorLinkForm%', 'wsal-gravity-forms' ),
				'gravityforms_fields',
				'created',
			),

			array(
				5709,
				WSAL_LOW,
				__( 'A form was submitted', 'wsal-gravity-forms' ),
				__( 'Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% Submission email: %email% %LineBreak% %EntryLink%', 'wsal-gravity-forms' ),
				'gravityforms_forms',
				'duplicated',
			),

			/*
			 * Form confirmations.
			 */
			array(
				5705,
				WSAL_MEDIUM,
				__( 'A confirmation was created, modified or deleted', 'wsal-gravity-forms' ),
				__( 'Confirmation name: %confirmation_name% %LineBreak% Confirmation type: %confirmation_type% %LineBreak% Confirmation message: %confirmation_message% %LineBreak% Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EditorLinkForm%', 'wsal-gravity-forms' ),
				'gravityforms_confirmations',
				'created',
			),

			array(
				5708,
				WSAL_LOW,
				__( 'A confirmation was activated or deactivated', 'wsal-gravity-forms' ),
				__( 'The confirmation %confirmation_name% in the form %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EditorLinkForm%', 'wsal-gravity-forms' ),
				'gravityforms_confirmations',
				'created',
			),

			/*
			 * Form notifications.
			 */
			array(
				5706,
				WSAL_MEDIUM,
				__( 'A notification was created, modified or deleted', 'wsal-gravity-forms' ),
				__( 'Notification name: %notification_name% %LineBreak% Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EditorLinkForm%', 'wsal-gravity-forms' ),
				'gravityforms_notifications',
				'created',
			),

			array(
				5707,
				WSAL_LOW,
				__( 'A notification was activated or deactivated', 'wsal-gravity-forms' ),
				__( 'Notification name: %notification_name% %LineBreak% Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EditorLinkForm%', 'wsal-gravity-forms' ),
				'gravityforms_notifications',
				'activated',
			),

			/*
			 * Form entries.
			 */
			array(
				5710,
				WSAL_LOW,
				__( 'An entry was starred or unstarred', 'wsal-gravity-forms' ),
				__( 'Entry title: %entry_title% %LineBreak% Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EntryLink%', 'wsal-gravity-forms' ),
				'gravityforms_entries',
				'starred',
			),

			array(
				5711,
				WSAL_LOW,
				__( 'An entry was marked as read or unread', 'wsal-gravity-forms' ),
				__( 'Entry title: %entry_title% %LineBreak% Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EntryLink%', 'wsal-gravity-forms' ),
				'gravityforms_entries',
				'read',
			),

			array(
				5712,
				WSAL_MEDIUM,
				__( 'An entry was moved to trash', 'wsal-gravity-forms' ),
				__( 'An entry was %event_desc% %LineBreak% Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EntryLink%', 'wsal-gravity-forms' ),
				'gravityforms_entries',
				'read',
			),

			array(
				5713,
				WSAL_MEDIUM,
				__( 'An entry was permanently deleted', 'wsal-gravity-forms' ),
				__( 'Permanently deleted the entry %entry_title% %LineBreak% Form name: %form_name% %LineBreak% Form ID: %form_id%', 'wsal-gravity-forms' ),
				'gravityforms_entries',
				'read',
			),

			array(
				5714,
				WSAL_MEDIUM,
				__( 'An entry note was created or deleted', 'wsal-gravity-forms' ),
				__( 'The entry note %entry_note% %LineBreak% Entry title: %entry_title% %LineBreak% Form name: %form_name% %LineBreak% Form ID: %form_id% %LineBreak% %EntryLink%', 'wsal-gravity-forms' ),
				'gravityforms_entries',
				'read',
			),

			/*
			 * Settings.
			 */
			array(
				5716,
				WSAL_HIGH,
				__( 'A plugin setting was changed.', 'wsal-gravity-forms' ),
				__( 'The plugin setting %setting_name% %LineBreak% Previous value %old_value% %LineBreak% New value: %new_value% %LineBreak%', 'wsal-gravity-forms' ),
				'gravityforms_settings',
				'modified',
			),

		),
	),
);
