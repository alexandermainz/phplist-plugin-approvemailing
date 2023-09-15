<?php
/**
 * This file contains the english text strings.
 *
 * @category  phplist
 */

/*
 *    Important - this file must be saved in UTF-8 encoding
 *
 */

 $lan = array(
   'plugin_title' => 'Approve Mailings Plugin',
   'plugin_description' => 'Plugin for phpList offering a workflow for approval of a mailing before it can be sent out',
   'Approval' => 'Approval',
   'ListsNeedingApprovement' => 'IDs of lists that need approvement when sent to, separated by comma',
   'ApproverEmail' => 'Default E-Mail address to send approval requests to',
   'msg_needs_approval' => 'This mailing needs approval before it can be sent out. This must be done by a different administrator than the one who created the campaign.',
   'status_approved' => 'approved',
   'status_not_approved' => 'needs approval',
   'err_not_approved' => 'This mailing has not yet been approved by another administrator and is not allowed to be sent out.',
   'approval_request_sent' => 'An approval request has been sent to %s!',
   'msg_approved_by' => 'Mailing campaign has been approved by administrator #%d.',
   'err_no_admin' => 'Could not approve mailing campaign: Admin ID not found.',
   'btn_approve' => 'Approve mailing campaign',
   'send_approval_to' => 'Send approval request to:',
   'btn_request' => 'Request approval',
   'already_approved_by' => 'This message has already been approved by administrator #%d',
   'no_approval_needed' => 'The selected mailing list(s) do not need approval.',
   'mail_subject' => 'Approval request for mailing "%s"',
   'mail_body_1' => 'Please approve the mailing "%s" for sending.'."\r\n\r\nYou can approve the mailing by clicking the link below:\r\n",
   'mail_body_2' => "\r\n\r\nThis email was sent automatically by phpList on behalf of %s from %s.",

);
 ?>
 