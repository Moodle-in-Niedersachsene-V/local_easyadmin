<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English strings for local_easyadmin.
 *
 * @package    local_easyadmin
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accessescount_easyadmin'] = 'Accesses (last 7 days): {$a}';
$string['accessescount_unavailable'] = 'Access statistics are currently unavailable on this site (standard log store not active).';
$string['activeuserscount_easyadmin'] = 'Active users (last 7 days): {$a}';
$string['allowframembedding_easyadmin'] = 'Allow this site to be embedded in other websites (frames/iFrames)';
$string['allowframembedding_easyadmin_help'] = 'Warning: this should usually stay disabled, as it can make so-called clickjacking attacks easier, where your site is invisibly embedded into another website.';
$string['archive_alreadyhidden'] = '(already hidden)';
$string['archive_cancel_button'] = 'Cancel';
$string['archive_choosecategory_button'] = 'Show courses in this category';
$string['archive_confirm_button'] = 'Yes, archive now';
$string['archive_confirm_intro'] = 'The following courses will be hidden and moved into the archive category. This action affects multiple courses at once - please review the list carefully:';
$string['archive_continue_button'] = 'Continue to confirmation';
$string['archive_defaultcategoryname'] = 'Archive';
$string['archive_nocategories'] = 'No course categories exist yet.';
$string['archive_nocoursesincategory'] = 'There are currently no courses in this category.';
$string['archive_targetcategoryname_easyadmin'] = 'Target category for the archive';
$string['archived_success'] = '{$a} course(s) have been successfully archived.';
$string['archiveheader'] = 'Archive courses';
$string['archiveintro'] = 'Hides selected courses and moves them into an archive category. Ideal at the end of the school year.';
$string['backgroundcolor'] = 'Background colour (if no background image)';
$string['backgroundcolor_help'] = 'Only used while no background image has been uploaded.';
$string['backgroundimage'] = 'Background image';
$string['backgroundimage_help'] = 'Shown as the background of the whole Moodle site. Overrides the background colour while an image is set.';
$string['backtocourses_easyadmin'] = 'Back to courses overview';
$string['backtodashboard'] = 'Back to overview';
$string['backtousers_easyadmin'] = 'Back to users & groups overview';
$string['backup_confirm_button'] = 'Back up now';
$string['backup_confirm_intro'] = 'A full backup will now be created for the following courses. Depending on course size, this may take a few minutes:';
$string['backup_done_intro'] = 'The backup is complete. Click a file below to download it:';
$string['backup_downloadall_button'] = 'Download all as ZIP';
$string['backup_downloadlink'] = 'Download backup: {$a}';
$string['backup_downloadlink_notice'] = 'Note: the backup files are stored in this plugin\'s own, protected file area and can only be accessed by users with access to this tile.';
$string['backup_retention_notice'] = 'Backup files are automatically deleted after 20 days (nightly cleanup task).';
$string['backupheader'] = 'Back up courses';
$string['backupintro'] = 'Creates a full backup (.mbz) of individual courses for direct download - unlike the automated backup, this file is not placed in the shared collection area and can be restored specifically for just this one course, without a full server restore.';
$string['bulkcategory_create_button'] = 'Create categories';
$string['bulkcategory_created_easyadmin'] = '{$a->created} categor(y/ies) were created, {$a->skipped} already existing were skipped.';
$string['bulkcategory_names_easyadmin'] = 'Names (one entry per line)';
$string['bulkcategory_names_easyadmin_help'] = 'One name/abbreviation per line, e.g.:
AM
BC
DE
Subcategories that already exist with the same name under the same parent category are skipped, so no duplicates are created.';
$string['bulkcategory_parent_easyadmin'] = 'Parent category';
$string['bulkcategory_parent_easyadmin_help'] = 'All names entered below will be created as subcategories of this category.';
$string['bulkcategoryheader'] = 'Create several subcategories at once';
$string['bulkcategoryintro'] = 'Handy for e.g. one subcategory per teacher abbreviation under a parent category "Teachers" - just enter one name/abbreviation per line.';
$string['bulkcohort_create_button'] = 'Create global groups';
$string['bulkcohort_created_easyadmin'] = '{$a->created} global group(s) were created, {$a->skipped} already existing were skipped.';
$string['bulkcohort_names_easyadmin'] = 'Names (one entry per line)';
$string['bulkcohort_names_easyadmin_help'] = 'One name per line, e.g.:
Class 3a
Class 3b
Global groups that already exist with the same name are skipped, so no duplicates are created.';
$string['bulkcohortheader'] = 'Create several global groups at once';
$string['bulkcohortintro'] = 'Creates several global groups (cohorts) at the same time.';
$string['bulkcourse_create_button'] = 'Create courses';
$string['bulkcourse_created_easyadmin'] = '{$a->created} course(s) were created, {$a->skipped} skipped (short name already taken), {$a->invalid} line(s) in the wrong format ignored.';
$string['bulkcourse_enrolnotice_easyadmin'] = 'Applies to ALL courses created above in this batch.';
$string['bulkcourse_entries_easyadmin'] = 'Courses (one entry per line)';
$string['bulkcourse_entries_easyadmin_help'] = 'Format per line: Full name;Short name, e.g.:
Maths class 3a;MA3A
English class 3a;EN3A
Lines with an already-used short name are skipped, so no duplicates are created.';
$string['bulkcourseheader'] = 'Create several courses at once';
$string['bulkcourseintro'] = 'Creates several courses at once in the same category.';
$string['bulkuser_create_button'] = 'Create users';
$string['bulkuser_created_easyadmin'] = '{$a->created} user(s) were created, {$a->skipped} skipped (username/email already taken or invalid), {$a->invalid} line(s) in the wrong format ignored.';
$string['bulkuser_entries_easyadmin'] = 'Users (one entry per line)';
$string['bulkuser_entries_easyadmin_help'] = 'Format per line: First name;Last name;Email;Username, e.g.:
Anna;Sample;anna.sample@school.org;asample
Ben;Example;ben.example@school.org;bexample
Lines with an already-used username or an invalid/already-used email address are skipped. This method never grants administrator rights - use the single form above for that.';
$string['bulkuser_passwordnotice'] = 'Important: note down the following passwords now - for security reasons they are not stored anywhere and cannot be shown again later. All accounts must change their password on first login.';
$string['bulkuserheader'] = 'Create several users at once';
$string['bulkuserintro'] = 'Creates several manual user accounts at the same time. Passwords are generated automatically and shown once after creation.';
$string['cachesheader'] = 'Purge caches';
$string['cachesintro'] = 'If changes (e.g. to the design) do not appear immediately, purging the caches can help.';
$string['cachespurged'] = 'The caches have been purged.';
$string['categoriesheader'] = 'Create categories';
$string['categoriesintro'] = 'Categories help keep courses organised - e.g. by year group or subject.';
$string['categorycount_easyadmin'] = 'Currently existing categories: {$a}';
$string['categorycreated_easyadmin'] = 'The category "{$a}" has been created.';
$string['categorydescription_easyadmin'] = 'Description (optional)';
$string['categoryname_easyadmin'] = 'Category name';
$string['categoryparent_easyadmin'] = 'Parent category';
$string['categoryparent_top_easyadmin'] = 'None (top level)';
$string['categoryrole_header_easyadmin'] = 'Assign a role in this category (optional)';
$string['categoryrole_notassigned_easyadmin'] = 'Note: the role could not be assigned (unknown role or missing permission).';
$string['categoryrole_role_coursecreator_easyadmin'] = 'Course creator';
$string['categoryrole_role_easyadmin'] = 'Role';
$string['categoryrole_role_easyadmin_help'] = 'Which role the selected person receives in the context of this category. "Course creator" may create courses in the category. "Manager" has broader management rights in the category. Only roles you are allowed to assign can be granted.';
$string['categoryrole_role_manager_easyadmin'] = 'Manager';
$string['categoryrole_user_easyadmin'] = 'Person';
$string['categoryrole_user_easyadmin_help'] = 'Optional: choose a person who should automatically receive a role in the newly created category. Leave empty to assign no role.';
$string['categoryrole_user_none_easyadmin'] = '— none (no role assignment) —';
$string['cohortcount_easyadmin'] = 'Currently existing global groups: {$a}';
$string['cohortcreated_easyadmin'] = 'The global group "{$a}" has been created.';
$string['cohortdescription_easyadmin'] = 'Description (optional)';
$string['cohortenrol_easyadmin'] = 'Link global group as participants';
$string['cohortenrol_easyadmin_help'] = 'Automatically links a global group (cohort) to the course - all current and future members will continuously be enrolled as participants, without you having to maintain this manually.';
$string['cohortidnumber_easyadmin'] = 'ID number (optional)';
$string['cohortidnumber_easyadmin_help'] = 'A unique internal identifier, e.g. for import/export. Can be left empty.';
$string['cohortname_easyadmin'] = 'Group name';
$string['cohortoption_none_easyadmin'] = 'None';
$string['cohortsheader'] = 'Global groups';
$string['cohortsintro'] = 'Cohorts are groups that apply across courses - e.g. a whole class that is a member of several courses at once.';
$string['communicationintro'] = 'Your site\'s communication features in one place.';
$string['communicationsaved'] = 'The communication settings have been saved.';
$string['coursecategory_easyadmin'] = 'Category';
$string['coursecount_easyadmin'] = 'Total courses: {$a}';
$string['coursecreated_easyadmin'] = 'The course "{$a}" has been created.';
$string['coursesdashboardintro'] = 'Choose an area.';
$string['coursevisible_easyadmin'] = 'Visible to participants';
$string['createcategory_easyadmin'] = 'Create category';
$string['createcohort_easyadmin'] = 'Create global group';
$string['createcourse_easyadmin'] = 'Create course';
$string['createcoursesheader'] = 'Create courses';
$string['createcoursesintro'] = 'Creates a new course.';
$string['createuser_easyadmin'] = 'Create user account';
$string['createusersheader'] = 'Create new user account';
$string['createusersintro'] = 'Creates a new, manual user account (no SSO/LDAP login etc.).';
$string['dashboardtitle'] = 'School Administration';
$string['debugmode_active_notice'] = 'Warning: Debug mode is currently active. Please remember to disable it again after troubleshooting.';
$string['debugmode_enabled'] = 'Enable debug mode';
$string['debugmode_enabled_help'] = 'Shows detailed technical error messages on the site. Only enable briefly for targeted troubleshooting and switch off again afterwards.';
$string['debugmode_warning'] = 'Important: Debug mode shows additional technical information that is helpful for troubleshooting but can also reveal sensitive details. Please make sure to disable it again after troubleshooting.';
$string['delete_acknowledge_easyadmin'] = 'I understand that these courses will be permanently and irreversibly deleted.';
$string['delete_confirm_button'] = 'Delete permanently';
$string['delete_confirm_intro'] = 'The following courses will be PERMANENTLY deleted, including all content, participant data, and grades:';
$string['deleted_success_easyadmin'] = '{$a} course(s) have been permanently deleted.';
$string['deleteheader'] = 'Delete courses';
$string['deleteintro'] = 'Warning: deleted courses cannot be restored. Consider "Archive courses" above instead if you are not completely sure, or create a backup under "Back up courses" beforehand.';
$string['designintro'] = 'Customise the appearance of your Moodle site. Changes take effect immediately across the whole site.';
$string['designsaved'] = 'The design settings have been saved.';
$string['displayloginfailures_easyadmin'] = 'Notify users about failed login attempts';
$string['displayloginfailures_easyadmin_help'] = 'Shows users, after logging in, how many failed login attempts were made against their account since their last login.';
$string['easyadmin:managecommunication'] = 'Manage communication features via the dashboard';
$string['easyadmin:managecourses'] = 'Manage courses via the dashboard';
$string['easyadmin:managedesign'] = 'Change design settings via the dashboard';
$string['easyadmin:managemaintenance'] = 'Use maintenance functions and reports via the dashboard';
$string['easyadmin:managenews'] = 'Manage front page settings via the dashboard';
$string['easyadmin:manageprivacy'] = 'Manage security and privacy settings via the dashboard';
$string['easyadmin:manageschooltiles'] = 'Open the school tiles settings via the dashboard';
$string['easyadmin:manageusers'] = 'Manage users/groups via the dashboard';
$string['easyadmin:view'] = 'Access the school administration dashboard';
$string['emailclient_enabled_easyadmin'] = 'Enable the mail system';
$string['emailclient_enabled_easyadmin_help'] = 'Enables or disables the internal mail system for users. When disabled, the e-mail client is hidden from the navigation and access is blocked for everyone except administrators.';
$string['emailclient_fullsettings_easyadmin'] = 'Open all mail settings';
$string['emailclientheader'] = 'Email client (optional add-on)';
$string['enrolheader_easyadmin'] = 'Enrolment (optional)';
$string['error_autoclose_range'] = 'Please provide a value between 0 and 120 seconds.';
$string['error_bulkcategory_toomany'] = 'Please enter no more than 200 entries at once.';
$string['error_cohortidnumbertaken'] = 'This ID number is already in use.';
$string['error_deletenotacknowledged'] = 'Please check "I understand..." to confirm the deletion.';
$string['error_emailtaken'] = 'This email address is already used by another account.';
$string['error_invalidcolor'] = 'Please provide a valid colour in the format #RRGGBB.';
$string['error_invalidemail'] = 'Please provide a valid email address.';
$string['error_invalidurl'] = 'Please provide a valid address on this website.';
$string['error_invalidusername'] = 'The username may only contain lower-case letters, digits, and . - _ @.';
$string['error_lockoutthreshold_range'] = 'Please provide a value between 1 and 100.';
$string['error_minpasswordlength_range'] = 'Please provide a value between 4 and 64.';
$string['error_nocoursesselected'] = 'No course was selected.';
$string['error_notargetcategory'] = 'Please select a valid target category.';
$string['error_passwordpart_range'] = 'Please provide a value between 0 and 20.';
$string['error_required'] = 'This field is required.';
$string['error_shortnametaken_easyadmin'] = 'This short name is already used by another course.';
$string['error_teachernotfound_easyadmin'] = 'No user account was found with this username or email address.';
$string['error_usernametaken'] = 'This username is already taken.';
$string['field_logo'] = 'School logo';
$string['field_logo_help'] = 'Shown in the header, on the login page and in emails/PDF documents. Recommended: PNG with a transparent background.';
$string['forcelogin_easyadmin'] = 'Require login for the whole site';
$string['forcelogin_easyadmin_help'] = 'If enabled, visitors who are not logged in cannot see any content on the site (not even the front page) - they are sent straight to the login page.';
$string['forcepasswordchange_easyadmin'] = 'Require password change on first login';
$string['frontpage_easyadmin'] = 'View before logging in';
$string['frontpage_easyadmin_help'] = 'What visitors who are not logged in see on the front page. Note: if multiple views were previously combined in Site administration, saving here will use only the single option selected.';
$string['frontpageintro'] = 'Basic information about your site, and what visitors see on the front page before and after logging in.';
$string['frontpageloggedin_easyadmin'] = 'View after logging in';
$string['frontpageloggedin_easyadmin_help'] = 'What logged-in users see on the front page. Note: if multiple views were previously combined in Site administration, saving here will use only the single option selected.';
$string['frontpageoption_allcourses'] = 'List of all courses';
$string['frontpageoption_categories'] = 'Category names';
$string['frontpageoption_combo'] = 'Combined category/course list';
$string['frontpageoption_coursesearch'] = 'Course search';
$string['frontpageoption_enrolledcourses'] = 'List of enrolled courses';
$string['frontpageoption_news'] = 'Latest announcements';
$string['frontpageoption_none'] = 'No display';
$string['frontpagesaved'] = 'The front page settings have been saved.';
$string['frontpagesummary_easyadmin'] = 'Site information';
$string['frontpagesummary_easyadmin_help'] = 'Short description of your school/site. Note: only plain text is supported here; for images or formatting, use Site administration > Front page > Front page settings.';
$string['guestloginbutton_easyadmin'] = 'Show guest login button on the login page';
$string['guestloginbutton_easyadmin_help'] = 'Shows an extra button on the login page that lets visitors without an account log in as a guest (with restricted access).';
$string['localmail_fullsettings_easyadmin'] = 'Open all mail settings';
$string['localmail_status_easyadmin'] = 'Internal email system (local_mail). Access is managed via Moodle role administration.';
$string['localmailheader'] = 'Email';
$string['lockoutthreshold_easyadmin'] = 'Lock account after this many failed login attempts';
$string['lockoutthreshold_easyadmin_help'] = 'After this number of failed login attempts, the affected account is temporarily locked to protect it against password guessing.';
$string['loginpage_hidedefaultimage'] = 'Hide/replace the default login page image';
$string['loginpage_hidedefaultimage_help'] = "Hides Moodle's default image shown on the login page. If a custom image is also uploaded, it is shown instead. Note: the exact effect may vary slightly between Moodle versions, as the login page is still technically evolving.";
$string['loginpage_image'] = 'Custom login page image';
$string['loginpage_image_help'] = 'Replaces the default login page image with a custom one, e.g. a school photo.';
$string['loginpage_instructions'] = 'Instructions on the login page';
$string['loginpage_instructions_help'] = 'This text appears next to the login form in a grey box, for example a short note about who logs in here or whom to contact if there are problems.

It is the same setting as "Instructions" under Site administration → Authentication → Manage authentication. Changes take effect on the real login page immediately.

If the field is left empty, Moodle shows its own default text again.';
$string['loginpageheader'] = 'Login page';
$string['loglifetime_easyadmin'] = 'Keep log data for';
$string['loglifetime_easyadmin_help'] = 'Determines how long Moodle keeps log data (who did what and when) before it is automatically deleted. For data protection reasons, this selection deliberately does not offer a "Never delete" option. Values below 30 days are not recommended, as this can make statistics unreliable.';
$string['logretention_current'] = 'Current value: {$a} days';
$string['logretention_days'] = '{$a} days';
$string['logretention_never'] = 'Never delete';
$string['logretention_never_current'] = 'Current: Never delete (please change for data protection reasons)';
$string['logretentionheader'] = 'Log retention';
$string['maintenanceheader'] = 'Troubleshooting (debug mode)';
$string['maintenancesaved'] = 'The maintenance settings have been saved.';
$string['makeadmin_easyadmin'] = 'Make this person a site administrator';
$string['makeadmin_easyadmin_help'] = 'Only visible if you yourself already have full administrator access. Grants the new person the same, unrestricted rights as yourself.';
$string['makeadmin_warning'] = 'Warning: this grants full administrator access to the entire site. Please only grant this if truly necessary. For security reasons, administrator accounts should always be manual accounts (no SSO) - this is automatically the case for an account created through this form.';
$string['managecohorts_easyadmin'] = 'Fully manage global groups (add members, import, etc.)';
$string['managecohorts_easyadmin_notice'] = 'Note: full management additionally requires the "moodle/cohort:manage" capability alongside this dashboard\'s permissions.';
$string['manualaccount_notice'] = 'This form only creates manual accounts - i.e. accounts with their own username and password, independent of any single sign-on login that may also be in use.';
$string['maxconsecutiveidentchars_easyadmin'] = 'Consecutive identical characters';
$string['maxconsecutiveidentchars_easyadmin_help'] = 'Passwords may contain at most this many consecutive identical characters. A value of 0 disables this check.';
$string['messaging_easyadmin'] = 'Enable the messaging system';
$string['messaging_easyadmin_help'] = 'Turns Moodle\'s built-in messaging system on or off (the speech-bubble icon at the top of the page). When disabled, users can no longer send each other internal messages. Equivalent to "Enable messaging system" under Site administration → General → Advanced features.';
$string['messagingheader'] = 'Messaging';
$string['minpassworddigits_easyadmin'] = 'Digits';
$string['minpassworddigits_easyadmin_help'] = 'Passwords must contain at least this many digits.';
$string['minpasswordlength_easyadmin'] = 'Minimum password length';
$string['minpasswordlower_easyadmin'] = 'Lower case letters';
$string['minpasswordlower_easyadmin_help'] = 'Passwords must contain at least this many lower case letters.';
$string['minpasswordnonalphanum_easyadmin'] = 'Special characters';
$string['minpasswordnonalphanum_easyadmin_help'] = 'Passwords must contain at least this many special (non-alphanumeric) characters.';
$string['minpasswordupper_easyadmin'] = 'Upper case letters';
$string['minpasswordupper_easyadmin_help'] = 'Passwords must contain at least this many upper case letters.';
$string['move_confirm_button'] = 'Move now';
$string['move_confirm_intro'] = 'The following courses will be moved into the category "{$a}":';
$string['move_targetcategory_easyadmin'] = 'Target category';
$string['moved_success_easyadmin'] = '{$a} course(s) have been moved.';
$string['moveheader'] = 'Move courses';
$string['moveintro'] = 'Moves selected courses into a different category.';
$string['nopermissiontiles'] = 'No sections are currently enabled for your account. Please contact Moodle in Niedersachsen e. V. if you need access.';
$string['pagewidth'] = 'Maximum page width';
$string['pagewidth_default'] = 'Default (Moodle default)';
$string['pagewidth_full'] = 'Full width (100%)';
$string['pagewidth_help'] = 'Adjusts the maximum width of the content area without having to edit SCSS code. "Default" leaves the width defined by Moodle or the theme unchanged.';
$string['pagewidth_medium'] = 'Medium (1200px)';
$string['pagewidth_wide'] = 'Wide (1400px)';
$string['password_easyadmin'] = 'Password';
$string['password_easyadmin_help'] = 'Must meet the password policy set for this site (see the "Security and privacy" tile).';
$string['passwordpolicy_easyadmin'] = 'Enable password rules';
$string['passwordpolicy_easyadmin_help'] = 'Enforces a minimum length for new or changed passwords (see field below).';
$string['placeholder_comingsoon'] = 'This section is currently being set up and will be available soon.';
$string['pluginname'] = 'Easy School Administration';
$string['policytool_heading'] = 'Detailed policies (optional)';
$string['policytool_hint'] = 'Moodle has its own management for full documents such as a privacy policy or terms of use, including versioning and mandatory acceptance at login. Note: this requires the "tool/policy:managedocs" capability in addition to this dashboard\'s permissions - if the button below shows "No access", ask your technical support to add this capability.';
$string['policytool_linktext'] = 'Go to Moodle policy management';
$string['privacy:metadata'] = 'The Easy School Administration plugin does not store any permanent user profile data itself. However, the "Back up courses" tile temporarily (max. 20 days) stores course backup files in its own file area, which may contain participant data managed via the core_files subsystem.';
$string['privacyheader'] = 'Privacy';
$string['purgecachesbutton'] = 'Purge caches now';
$string['reportsheader'] = 'Reports';
$string['reportsintro'] = 'A short overview of your site\'s usage over the last 7 days.';
$string['schooltile_add'] = 'Add new tile';
$string['schooltile_categoryid'] = 'Course category';
$string['schooltile_cohort'] = 'Visible to global group';
$string['schooltile_cohort_all'] = 'Everyone sees this tile';
$string['schooltile_cohort_help'] = 'Only members of this global group see the tile on the front page. People with permission to manage school tiles always see all tiles regardless (for review purposes).';
$string['schooltile_color'] = 'Colour';
$string['schooltile_color_help'] = 'Background colour of the tile.';
$string['schooltile_delete'] = 'Delete';
$string['schooltile_delete_confirm'] = 'Really delete this tile?';
$string['schooltile_deleted'] = 'The tile has been deleted.';
$string['schooltile_edit'] = 'Edit';
$string['schooltile_editheader'] = 'Edit tile';
$string['schooltile_icon'] = 'Icon (emoji)';
$string['schooltile_icon_help'] = 'Shown as long as no custom image has been uploaded.';
$string['schooltile_image'] = 'Custom image (optional)';
$string['schooltile_image_help'] = 'Replaces the icon/emoji, if uploaded.';
$string['schooltile_managelink'] = 'Edit school tiles';
$string['schooltile_name'] = 'Tile name';
$string['schooltile_name_help'] = 'Shown on the tile itself, e.g. "Library" or "Class 3a".';
$string['schooltile_save'] = 'Save tile';
$string['schooltile_saved'] = 'The tile has been saved.';
$string['schooltile_targetheader'] = 'Tile target';
$string['schooltile_targettype'] = 'Target type';
$string['schooltile_targettype_category'] = 'Course category';
$string['schooltile_targettype_help'] = 'Determines where the tile leads: either directly to a course category, or to a freely chosen address.';
$string['schooltile_targettype_url'] = 'Custom address';
$string['schooltile_textcolor'] = 'Text colour';
$string['schooltile_textcolor_help'] = 'Colour of the text on the tile.';
$string['schooltile_url'] = 'Address (URL)';
$string['schooltile_url_help'] = 'Only used if "Custom address" is selected above. Can be a full web address or a Moodle-internal path (e.g. /course/index.php).';
$string['schooltiles_disable'] = 'Hide tiles';
$string['schooltiles_enable'] = 'Show tiles';
$string['schooltiles_maxreached'] = 'The maximum of 8 tiles has been reached.';
$string['schooltiles_none'] = 'No tiles have been created yet.';
$string['schooltiles_status_disabled'] = 'Currently hidden';
$string['schooltiles_status_enabled'] = 'Currently visible';
$string['schooltilesintro'] = 'Up to 8 tiles shown on the front page below the usual content - e.g. as quick access to course categories or external addresses.';
$string['securityheader'] = 'Security';
$string['securityintro'] = 'Important security and privacy settings for your site, all in one place.';
$string['securitysaved'] = 'The security and privacy settings have been saved.';
$string['selectall_easyadmin'] = 'Select/deselect all';
$string['sitepolicy_easyadmin'] = 'Link to privacy policy';
$string['sitepolicy_easyadmin_help'] = 'Address (URL) of your privacy policy. Users must agree to it on first login if an address is set here. Note: this is a simple link. For full documents with versioning and mandatory acceptance, use the Moodle policy management tool (button above).';
$string['sitepolicyhandler_usetool'] = 'Enable advanced policy management';
$string['sitepolicyhandler_usetool_help'] = 'Must be enabled for the "Go to Moodle policy management" button below to work - otherwise it shows a "Section error". While this is unchecked, Moodle instead keeps using the simple link in the "Link to privacy policy" field further below.';
$string['subtile_archive_desc'] = 'End of school year';
$string['subtile_backup_desc'] = 'For download';
$string['subtile_categories_desc'] = 'Single or as a list';
$string['subtile_cohorts_desc'] = 'Single or as a list';
$string['subtile_createcourses_desc'] = 'Single or as a list';
$string['subtile_createusers_desc'] = 'Single or as a list';
$string['subtile_delete_desc'] = 'Remove permanently';
$string['subtile_move_desc'] = 'To another category';
$string['summary_blockhint'] = 'Important: This text only becomes visible if the "Course/site summary" block is shown on the front page. The checkbox below takes care of that automatically.';
$string['summary_showblock'] = 'Automatically show the "Course/site summary" block on the front page';
$string['summary_showblock_help'] = 'Enables the "Course/site summary" block (if needed) and places it on the front page, so the site information entered above becomes visible to visitors. If such a block already exists, a second one will not be added. Note: position and order of blocks can still be adjusted afterwards via the front page\'s normal edit mode.';
$string['task_cleanup_course_backups'] = 'Delete old course backups (Courses tile)';
$string['teacheridentifier_easyadmin'] = 'Enrol teacher as trainer';
$string['teacheridentifier_easyadmin_help'] = 'Username or email address of an existing person. Will be enrolled into the course with the "Teacher" role. Leave empty to enrol no one.';
$string['textcolor'] = 'Text colour';
$string['textsize'] = 'Text size';
$string['textsize_large'] = 'Large';
$string['textsize_normal'] = 'Standard';
$string['textsize_small'] = 'Small';
$string['textsize_xlarge'] = 'Extra large';
$string['tile_communication_desc'] = 'Turn the messaging system on/off (email to follow)';
$string['tile_communication_title'] = 'Communication';
$string['tile_courses_desc'] = 'Create, manage and archive courses';
$string['tile_courses_title'] = 'Courses';
$string['tile_design_desc'] = 'Adjust logo, colours and appearance';
$string['tile_design_title'] = 'Design';
$string['tile_maintenance_desc'] = 'Debug mode, purge caches, simple access statistics';
$string['tile_maintenance_title'] = 'Maintenance & reports';
$string['tile_news_desc'] = 'Name, short name, description and front page display';
$string['tile_news_title'] = 'Front page';
$string['tile_privacy_desc'] = 'Review and adjust important security and privacy settings';
$string['tile_privacy_title'] = 'Security and privacy';
$string['tile_schooltiles_desc'] = 'Set up up to 8 front page tiles';
$string['tile_schooltiles_title'] = 'School tiles';
$string['tile_users_desc'] = 'Manage teachers, students and groups';
$string['tile_users_title'] = 'Users & groups';
$string['usercount_easyadmin'] = 'Total users: {$a}';
$string['usercreated_easyadmin'] = 'The user account for {$a} has been created.';
$string['username_easyadmin'] = 'Username';
$string['username_easyadmin_help'] = 'Only lower-case letters, digits, and the characters . - _ @ are allowed.';
$string['usersdashboardintro'] = 'Choose an area.';
$string['viewfulllog_easyadmin'] = 'View full log data';
$string['viewfulllog_easyadmin_notice'] = 'Note: the full log view additionally requires the "report/log:view" capability alongside this dashboard\'s permissions.';
$string['welcomeintro'] = 'Here you can manage the most important settings of your Moodle site - no prior knowledge required. Simply choose a section.';
$string['welcomepopup_autoclose'] = 'Auto-close after (seconds)';
$string['welcomepopup_autoclose_help'] = 'The popup closes itself after this time if nobody interacts with it. 0 = never auto-close (only via the X, background click, or login button).';
$string['welcomepopup_buttontext'] = 'Button text';
$string['welcomepopup_buttontext_default'] = 'Log in now';
$string['welcomepopup_buttonurl'] = 'Button target';
$string['welcomepopup_buttonurl_help'] = 'Where the button should lead. Default: the login page (/login/index.php). Only addresses on this site are allowed.';
$string['welcomepopup_enabled'] = 'Show welcome popup';
$string['welcomepopup_enabled_help'] = 'Shows a welcome window on page load for visitors who are not yet logged in, e.g. noting that this is a test version, with a login button. Does not appear on the login page itself.';
$string['welcomepopup_image'] = 'Image/icon';
$string['welcomepopup_image_help'] = 'Optional image shown in the popup, e.g. a school logo or mascot. If no image is uploaded, a school icon is shown instead.';
$string['welcomepopup_message'] = 'Message';
$string['welcomepopup_message_default'] = 'This is a test version of the primary school learning platform.';
$string['welcomepopup_title'] = 'Title';
$string['welcomepopup_title_default'] = 'Welcome!';
$string['welcomepopupheader'] = 'Welcome popup';
$string['welcometitle'] = 'Welcome to School Administration';
