# phplist-plugin-approvemailing
Plugin for phpList offering a workflow for approval of a mailing campaign before it can be sent out.

## Description
This plugin hooks into the "send campaign" page and provides an additonal tab for an approval workflow. If the campaign is aimed to one or more subscriber lists, that require approval, the campain cannot be queued for sending without an approval of a second administrator. Approval can be given by clicking the designated button on the "Approval" tab. An e-mail with an approval request can be sent out from the tab as well, so to explicitly request the necessary approval from another administrator.
In settings, you can configure the subscriber lists you want to require approval. A default recipient for approval request e-mails may also be configured, but can be overwritten with every request.

The plugin currently supports english and german UI language.

## Requirements & Installation
The plugin has been developed and tested with phpList version 3.6.13. No guarantee can be given, that the plugin will work with other releases than those tested.

For sending out approval request emails, the standard `mail()` function of your server's PHP instance will be used. If you want to use this feature, your server must be configured accordingly.

To install through phpList go to the Plugins page (menu Config > Manage Plugins) and use the package URL

`https://github.com/alexandermainz/phplist-plugin-approvemailing/archive/master.zip`

To install manually, download the plugin zip file from https://github.com/alexandermainz/phplist-plugin-approvemailing/archive/master.zip. Expand the zip file, then copy the contents of the `plugins` directory to your phpList `plugins` directory.

## Usage

### Configure the subscriber lists, that will need approval
Go to the Settings page (menu Config > Settings) and scroll down to the section "approvemailing settings".
In the setting "IDs of lists that need approvement when sent to, separated by comma", insert the list IDs of the appropriate lists. You can find the ID of a subscriber list on the right column in the table of the lists showed under Subscribers > Subscriber lists. If you like to include more than one list, separate the list ID numbers by comma.

### Configure a default e-mail recipient for approval requests (optional)
If you like, you may configure a default value for the e-mail address where approval request are sent to. The recipient of an approval request can also be entered/overwritten every time you send an approval request.
Go to the Settings page (menu Config > Settings) and scroll down to the section "approvemailing settings".
Add the appropriate e-mail address into the setting "Default E-Mail address to send approval requests to".

### Handle approvals and approval requests
Whenever you create a mailing campaign which aims to one or more subscriber lists, that require approval, the "Approval" tab page on the "send campaign" page will state, that this message needs approval. A button is presented to "Approve mailing campaign". Approval can only be done by a different administrator than that creating the campaign, thus, a 4-eyes principle is realised for sending the campaign. If you are allowed to approve the campaign, simply click that button to approve. After approval, the campaign my be queued for sending by any administrator.

Below the "Approval" button, a section is included where you can easily send out an approval request to your fellow administrator, telling her/him, that you have created a campaign that needs approval. Enter the e-mail address of the person the request should be sent to and click the "Request approval" button. An email containing a link to the approval page of the mailing campaign will be sent to the given recipient. The recipient must sign in to phpList with his credentials in order to perform the approval.

The approval tab will show a notification, if the subscriber list(s) included in a campaign do not require approval. A notification will also be shown, if a campaign already has been approved.

Additionally, you can see the approval status of a campaign in the campaigns table under Campaigns > List of campaigns in the column "Status".

