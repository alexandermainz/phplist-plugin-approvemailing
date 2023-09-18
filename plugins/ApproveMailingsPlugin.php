<?php
/**
 * ApproveMailingsPlugin for phplist.
 *
 * This file is a part of ApproveMailingsPlugin.
 *
 * ApproveMailingsPlugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * ApproveMailingsPlugin is distributed WITHOUT ANY WARRANTY; 
 * without even the implied warranty of  MERCHANTABILITY or FITNESS FOR 
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 *
 * @category  phplist
 *
 * @author    Alexander Schmitt
 * @copyright 2023 Alexander Schmitt
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

class ApproveMailingsPlugin extends phplistPlugin
{
    public $name = 'Approve Mailings Plugin';
    public $version = '1.0';
    public $authors = 'Alexander Schmitt';
    public $description = 'Plugin for phpList offering a workflow for approval of a mailing campaign before it can be sent out';
    public $enabled = true;
    public $coderoot = PLUGIN_ROOTDIR . '/ApproveMailingsPlugin/';
    public $needI18N = 1;

    private $i18n;

    /**
     * Settings of this plugin
     */
    public $settings = array(
        "ListsNeedingApprovement" => array (
          'value' => "",
          'description' => 'IDs of lists that need approvement when sent to, separated by comma',
          'type' => "text",
          'allowempty' => 1,
          "max" => 0,
          "min" => 0,
          'category'=> 'approvemailing',
        ),
        "ApproverEmail" => array (
            'value' => "",
            'description' => 'Default E-Mail address to send approval requests to',
            'type' => "text",
            'allowempty' => 1,
            "max" => 0,
            "min" => 0,
            'category'=> 'approvemailing',
        ),
    );

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Use this hook to set translatable text and retrieve config entries.
     */
    public function activate()
    {

        // set the i18n object and set the settings texts with the correct translations
        $this->i18n = new CommonPlugin_I18N($this);
        $this->settings['ListsNeedingApprovement']['description'] = $this->i18n->get('ListsNeedingApprovement');
        $this->settings['ApproverEmail']['description'] = $this->i18n->get('ApproverEmail');

        parent::activate();
    }

    /**
     * Provide "empty" admin menu instead of the "hello world" example from the base class.
     */
    public function adminmenu()
    {
        return array();
    }

    /**
     * This hook adds a tab to the send page, which allows the user to request approval for the mailing.
     */
    public function sendMessageTab($messageid = 0, $messagedata = array())
    {
        $approved = false;
        $adminID = $_SESSION['logindetails']['id'];  // currently logged in admin's ID

        // if the "Request approval" button has been clicked, send the approval request
        if (isset($_POST["requestApproval"])) {
            $this->sendApprovalRequest($messagedata);
            $s = sprintf($this->i18n->get('approval_request_sent'), $_POST["approverEmail"]);
            $html .= "<p><b>$s</b></p><br>";
        }

        // if the "Approve mailing campaign" button has been clicked, set the approver to the admin's ID
        if (isset($_POST["approve"])) {
            if (isset($adminID)) {
                $messagedata['approver'] = $adminID;
                setMessageData($messageid, 'approver', $adminID);
                $s = sprintf($this->i18n->get('msg_approved_by'), $adminID);
                $html .= "<p><b>$s</b></p><br>";
                $approved = true;
            }
            else
            {
                $html .= "<p><b>".$this->i18n->get('err_no_admin')."</b></p><br>";
            }
        }

        // if the mailing has not just been approved right now with the "Approve"-Button of this form,
        //  it must be checked if the mailing needs approval and appropriate info texts and buttons must be shown
        if (!$approved) {
            // check if the mailing needs approval
            $mustApprove = $this->mustApprove($messagedata);
            // needs approval, show the necessary info texts and buttons
            if ($mustApprove) {
                // check if the message has not already been approved by a different admin
                if (!isset($messagedata['approver']) || $messagedata['approver'] == $messagedata['owner']) {
                    // if not, show the approval button, the "Request approvement" button and an input field
                    //  for the email address where the request should be sent to
                    $html .= '<p>'.$this->i18n->get('msg_needs_approval').'</p>';
                    // the approval button will be disabled, if the logged in admin is the owner of the mailing
                    if ($messagedata['owner'] == $adminID)
                        $html .= '<p><input type="submit" name="approve" value="'.$this->i18n->get('btn_approve').'" disabled /></p><br>';
                    else
                        $html .= '<p><input type="submit" name="approve" value="'.$this->i18n->get('btn_approve').'" /></p><br>';

                    $html .= '<p>'.$this->i18n->get('send_approval_to').'<br><input type="text" size="30" maxLength="50" name="approverEmail" value="' . getConfig('ApproverEmail') . '" />&nbsp;';
                    $html .= '<input type="submit" name="requestApproval" value="'.$this->i18n->get('btn_request').'" /></p>';
                }
                else
                {
                    $s = sprintf($this->i18n->get('already_approved_by'), $messagedata['approver']);
                    $html .= "<p>$s</p>";
                }
            }
            else {
                $html = '<p>'.$this->i18n->get('no_approval_needed').'</p>';
            }
        }

        return $html;
    }

    /**
     * The tiotle for the additonal tab on the send page.
     */
    public function sendMessageTabTitle($messageid = 0)
    {
        return $this->i18n->get('Approval');
    }

    /**
     * allowMessageToBeQueued
     * called to verify that the message can be added to the queue
     * @param array messagedata - associative array with all data for campaign
     * @return empty string if allowed, or error string containing reason for not allowing
     */
    public function allowMessageToBeQueued($messagedata = array())
    {
        // check if the mailing is approved
        if (!$this->mustApprove($messagedata) ||
            isset($messagedata['approver']) && $messagedata['approver'] != $messagedata['owner'])
            return '';
        else
            return $this->i18n->get('err_not_approved');
    }

    /**
     * Send an approval request to the mail address specified in $_POST["approverEmail"]
     * or to the sender of the mailing if no approver email address is given.
     * The PHP mail() function is used for sending the email, thus must be configured correctly
     * in your PHP installation.
     */
    private function sendApprovalRequest($messagedata)
    {
        // get the approver's email address from the plugin settings
        $approverEmail = $_POST["approverEmail"];
        if ($approverEmail == '') {
            // if no approver email address is set, send the request to sender address of that mailing
            $approverEmail = $messagedata['fromemail'];
        }
        // send the approval request
        $subject = sprintf($this->i18n->get('mail_subject'), $messagedata["campaigntitle"]);
        $message = sprintf($this->i18n->get('mail_body_1'), $messagedata["campaigntitle"]);
        $message .= 'https://'.$_SERVER['SERVER_NAME'] . '/admin/?page=send&id=' . $messagedata["id"] . '&tab=Approval';
        $message .= sprintf($this->i18n->get('mail_body_2'), $messagedata['fromemail'], $_SERVER['SERVER_NAME']);
        $from = $messagedata['fromemail'];
        $to = $approverEmail;
        $headers = 'From: ' . $from . "\r\n" .
            'Reply-To: ' . $from . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        mail($to, $subject, $message, $headers);
    }

    
    /**
     * Return i18n language path where the transaltion files reside
     */
    public function i18nLanguageDir()
    {
        return dirname(__FILE__)."/ApproveMailingsPlugin/lan";
    }

    /**
     * messageStatus
     * @param int    $id     messageid
     * @param string $status message status
     *
     * @return possible additional text to display
     */
    public function messageStatus($id, $status)
    {
        // get the messagedata
        $messagedata = loadMessageData($id);
        // only show the status info, if the mailing needs approval
        if ($this->mustApprove($messagedata))
        {
            // add a status info text with the approval status
            if (isset($messagedata['approver']) && $messagedata['approver'] != $messagedata['owner'])
                return ' - '.$this->i18n->get('status_approved');
            else
                return ' - '.$this->i18n->get('status_not_approved');
        }
        return '';
    }

    /**
     * Check, if the given messsage needs approvement by checking, if it shall be sent to a list
     *  that needs approvement.
     */
    private function mustApprove($messagedata)
    {
        $mustApprove = false;
        // get the list of mailinglists-IDs that need approvement from the plugin settings
        $lists = getConfig('ListsNeedingApprovement');
        // convert the comma-separated list to an array
        $listArray = explode(",", $lists);
        // loop through the list array and check if each number is an index in the targetlist array
        foreach ($listArray as $number) {
            if (array_key_exists($number, $messagedata["targetlist"])) {
                $mustApprove = true;
                break;
            }
        }

        return $mustApprove;
    }

}
?>
