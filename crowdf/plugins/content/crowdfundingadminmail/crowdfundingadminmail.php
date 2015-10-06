<?php
/**
 * @package         Crowdfunding
 * @subpackage      Plugins
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

/**
 * This plugin send notification mails to the administrator.
 *
 * @package        Crowdfunding
 * @subpackage     Plugins
 */
class plgContentCrowdfundingAdminMail extends JPlugin
{
    /**
     * @var Prism\Log\Log
     */
    protected $log;

    /**
     * @var Joomla\Registry\Registry
     */
    public $params;

    protected $name = "Content - Crowdfunding Admin Mail";

    public function init()
    {
        jimport('Prism.init');
        jimport('Crowdfunding.init');
        jimport("EmailTemplates.init");

        // Prepare log object
        $registry = Joomla\Registry\Registry::getInstance("com_crowdfunding");
        /** @var $registry Joomla\Registry\Registry */

        $fileName  = $registry->get("logger.file");
        $tableName = $registry->get("logger.table");

        // Create log object
        $this->log = new Prism\Log\Log();

        // Set database writer.
        $this->log->addWriter(new Prism\Log\Writer\Database(JFactory::getDbo(), $tableName));

        // Set file writer.
        if (!empty($fileName)) {
            $file = JPath::clean(JFactory::getApplication()->get("log_path") . DIRECTORY_SEPARATOR . $fileName);
            $this->log->addWriter(new Prism\Log\Writer\File($file));
        }

        // Load language
        $this->loadLanguage();
    }

    /**
     * This method sends notification mail to the administrator when project owner publish a project.
     *
     * If I return NULL, an message will not be displayed in the browser.
     * If I return FALSE, an error message will be displayed in the browser.
     *
     * @param string $context
     * @param array  $ids
     * @param int    $state
     *
     * @return bool|null
     */
    public function onContentChangeState($context, $ids, $state)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        if (strcmp("com_crowdfunding.project", $context) != 0) {
            return null;
        }

        // Initialize plugin
        $this->init();

        // Check for enabled option for sending mail.
        // when user publish a project.
        $emailId = $this->params->get("send_when_published", 0);
        if (!$emailId) {
            $this->log->add(
                JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_EMAIL_TEMPLATE", $this->name),
                "PLG_CONTENT_ADMIN_EMAIL_ERROR",
                JText::_("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_EMAIL_TEMPLATE_NOTE")
            );

            return null;
        }

        Joomla\Utilities\ArrayHelper::toInteger($ids);

        if (!empty($ids) and $state == Prism\Constants::PUBLISHED) {

            $projects = $this->getProjectsData($ids);
            if (!$projects) {
                $this->log->add(
                    JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_PROJECTS", $this->name),
                    "PLG_CONTENT_ADMIN_EMAIL_ERROR",
                    JText::_("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_PROJECTS_NOTE")
                );

                return false;
            }

            // Load class CrowdfundingEmail.
            foreach ($projects as $project) {

                // Send email to the administrator.
                $result = $this->sendProjectMail($project, $emailId);

                // Check for error.
                if ($result !== true) {
                    return null;
                }
            }
        }

        return true;
    }

    /**
     * This method sends notification mail to the administrator when someone create a project.
     *
     * If I return NULL, an message will not be displayed in the browser.
     * If I return FALSE, an error message will be displayed in the browser.
     *
     * @param string  $context
     * @param CrowdfundingTableProject  $project
     * @param boolean $isNew
     *
     * @return null|boolean
     */
    public function onContentAfterSave($context, &$project, $isNew)
    {
        if (strcmp("com_crowdfunding.basic", $context) != 0) {
            return null;
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        // Initialize plugin
        $this->init();

        // Check for enabled option for sending mail
        // when user create a project.
        $emailId = $this->params->get("send_when_create", 0);
        if (!$emailId) {
            $this->log->add(
                JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_EMAIL_TEMPLATE", $this->name),
                "PLG_CONTENT_ADMIN_EMAIL_ERROR",
                JText::_("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_EMAIL_TEMPLATE_NOTE")
            );

            return null;
        }

        if (!empty($project->id) and $isNew) {

            // Send email to the administrator.
            $return = $this->sendProjectMail($project, $emailId);

            // Check for error.
            if ($return !== true) {
                $this->log->add(
                    JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_PROJECTS", $this->name),
                    "PLG_CONTENT_ADMIN_EMAIL_ERROR",
                    JText::_("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_PROJECTS_NOTE")
                );

                return null;
            }
        }

        return true;
    }

    /**
     * This method sends notification mail to the administrator when someone reports a project.
     *
     * If I return NULL, an message will not be displayed in the browser.
     * If I return FALSE, an error message will be displayed in the browser.
     *
     * @param string  $context
     * @param CrowdfundingTableReport  $report
     *
     * @return null|boolean
     */
    public function onContentAfterReport($context, &$report)
    {
        if (strcmp("com_crowdfunding.report", $context) != 0) {
            return null;
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if ($app->isAdmin()) {
            return null;
        }

        // Initialize plugin
        $this->init();

        // Check for enabled option for sending mail
        // when user reports a project.
        $emailId = $this->params->get("send_when_report", 0);
        if (!$emailId) {
            $this->log->add(
                JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_EMAIL_TEMPLATE", $this->name),
                "PLG_CONTENT_ADMIN_EMAIL_ERROR",
                JText::_("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_EMAIL_TEMPLATE_NOTE")
            );

            return null;
        }

        if (!empty($report->id)) {

            // Send email to the administrator.
            $return = $this->sendReportMail($report, $emailId);

            // Check for error.
            if ($return !== true) {
                $this->log->add(
                    JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_PROJECTS", $this->name),
                    "PLG_CONTENT_ADMIN_EMAIL_ERROR",
                    JText::_("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_INVALID_PROJECTS_NOTE")
                );

                return null;
            }
        }

        return true;
    }

    /**
     * Load data about projects
     *
     * @param array $ids
     *
     * @return array
     */
    private function getProjectsData($ids)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select("a.title");
        $query->select($query->concatenate(array("a.id", "a.alias"), ":") . " AS slug");
        $query->select($query->concatenate(array("b.id", "b.alias"), ":") . " AS catslug");

        $query
            ->from($db->quoteName("#__crowdf_projects", "a"))
            ->leftJoin($db->quoteName("#__categories", "b") . " ON a.catid = b.id")
            ->where("a.id IN (" . implode(",", $ids) . ")");

        $db->setQuery($query);
        $results = $db->loadObjectList();

        if (!$results) {
            $results = array();
        }

        return $results;
    }

    protected function sendProjectMail($project, $emailId)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Send mail to the administrator
        if (!$emailId) {
            return false;
        }

        // Get website
        $uri     = JUri::getInstance();
        $website = $uri->toString(array("scheme", "host"));

        $emailMode = $this->params->get("email_mode", "plain");

        // Prepare data for parsing
        $data = array(
            "site_name"  => $app->get("sitename"),
            "site_url"   => JUri::root(),
            "item_title" => $project->title,
            "item_url"   => $website . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($project->slug, $project->catslug)),
        );

        $email = new EmailTemplates\Email();
        $email->setDb(JFactory::getDbo());
        $email->load($emailId);

        if (!$email->getSenderName()) {
            $email->setSenderName($app->get("fromname"));
        }
        if (!$email->getSenderEmail()) {
            $email->setSenderEmail($app->get("mailfrom"));
        }

        // Prepare recipient data.
        $componentParams = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $componentParams Joomla\Registry\Registry */

        $recipientId = $componentParams->get("administrator_id");
        if (!empty($recipientId)) {
            $recipient     = JFactory::getUser($recipientId);
            $recipientName = $recipient->get("name");
            $recipientMail = $recipient->get("email");
        } else {
            $recipientName = $app->get("fromname");
            $recipientMail = $app->get("mailfrom");
        }

        // Prepare data for parsing
        $data["sender_name"]     = $email->getSenderName();
        $data["sender_email"]    = $email->getSenderEmail();
        $data["recipient_name"]  = $recipientName;
        $data["recipient_email"] = $recipientMail;

        $email->parse($data);
        $subject = $email->getSubject();
        $body    = $email->getBody($emailMode);

        $mailer = JFactory::getMailer();
        if (strcmp("html", $emailMode) == 0) { // Send as HTML message
            $result = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_HTML);
        } else { // Send as plain text.
            $result = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_PLAIN);
        }

        // Log the error.
        if ($result !== true) {
            $this->log->add(
                JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_SEND_MAIL", $this->name),
                "PLG_CONTENT_ADMIN_EMAIL_ERROR",
                JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_SEND_MAIL_NOTE", $mailer->ErrorInfo)
            );

            return false;
        }

        return true;
    }

    protected function sendReportMail($report, $emailId)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Send mail to the administrator
        if (!$emailId) {
            return false;
        }

        // Get website
        $uri     = JUri::getInstance();
        $website = $uri->toString(array("scheme", "host"));

        $emailMode = $this->params->get("email_mode", "plain");

        // Get project
        $project = Crowdfunding\Project::getInstance(JFactory::getDbo(), $report->project_id);

        // Prepare data for parsing
        $data = array(
            "site_name"  => $app->get("sitename"),
            "site_url"   => JUri::root(),
            "item_title" => $project->getTitle(),
            "item_url"   => $website . JRoute::_(CrowdfundingHelperRoute::getDetailsRoute($project->getSlug(), $project->getCatSlug())),
            "report_subject"     => $report->subject,
            "report_description" => $report->description
        );

        $email = new EmailTemplates\Email();
        $email->setDb(JFactory::getDbo());
        $email->load($emailId);

        if (!$email->getSenderName()) {
            $email->setSenderName($app->get("fromname"));
        }
        if (!$email->getSenderEmail()) {
            $email->setSenderEmail($app->get("mailfrom"));
        }

        // Prepare recipient data.
        $componentParams = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $componentParams Joomla\Registry\Registry */

        $recipientId = $componentParams->get("administrator_id");
        if (!empty($recipientId)) {
            $recipient     = JFactory::getUser($recipientId);
            $recipientName = $recipient->get("name");
            $recipientMail = $recipient->get("email");
        } else {
            $recipientName = $app->get("fromname");
            $recipientMail = $app->get("mailfrom");
        }

        // Prepare data for parsing
        $data["sender_name"]     = $email->getSenderName();
        $data["sender_email"]    = $email->getSenderEmail();
        $data["recipient_name"]  = $recipientName;
        $data["recipient_email"] = $recipientMail;

        $email->parse($data);
        $subject = $email->getSubject();
        $body    = $email->getBody($emailMode);

        $mailer = JFactory::getMailer();
        if (strcmp("html", $emailMode) == 0) { // Send as HTML message
            $result = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_HTML);
        } else { // Send as plain text.
            $result = $mailer->sendMail($email->getSenderEmail(), $email->getSenderName(), $recipientMail, $subject, $body, Prism\Constants::MAIL_MODE_PLAIN);
        }

        // Log the error.
        if ($result !== true) {
            $this->log->add(
                JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_SEND_MAIL", $this->name),
                "PLG_CONTENT_ADMIN_EMAIL_ERROR",
                JText::sprintf("PLG_CONTENT_CROWDFUNDINGADMINMAIL_ERROR_SEND_MAIL_NOTE", $mailer->ErrorInfo)
            );

            return false;
        }

        return true;
    }
}
