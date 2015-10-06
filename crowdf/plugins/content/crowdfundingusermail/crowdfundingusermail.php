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
 * Crowdfunding User Mail Plugin
 *
 * @package        Crowdfunding
 * @subpackage     Plugins
 */
class plgContentCrowdfundingUserMail extends JPlugin
{
    /**
     * @var Prism\Log\Log
     */
    protected $log;

    /**
     * @var Joomla\Registry\Registry
     */
    public $params;

    protected $name = "Content - Crowdfunding User Mail";

    public function init()
    {
        jimport("EmailTemplates.init");

        // Prepare log object
        $registry = Joomla\Registry\Registry::getInstance("com_crowdfunding");
        /** @var  $registry Joomla\Registry\Registry */

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
     * Send notification mail to a user when his project be approved.
     * If I return NULL, an message will not be displayed in the browser.
     * If I return FALSE, an error message will be displayed in the browser.
     *
     * @param string $context
     * @param array $ids
     * @param int $state
     *
     * @return bool|null
     */
    public function onContentChangeState($context, $ids, $state)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        if (!$app->isAdmin()) {
            return null;
        }

        if (strcmp("com_crowdfunding.project", $context) != 0) {
            return null;
        }

        // Initialize plugin
        $this->init();

        // Check for enabled option for sending mail
        // when administrator approve project.
        $emailId = $this->params->get("send_when_approved", 0);
        if (!$emailId) {
            $this->log->add(
                JText::sprintf("PLG_CONTENT_CROWDFUNDINGUSERMAIL_ERROR_INVALID_EMAIL_TEMPLATE", $this->name),
                "PLG_CONTENT_USERE_MAIL_ERROR",
                JText::_("PLG_CONTENT_CROWDFUNDINGUSERMAIL_ERROR_INVALID_EMAIL_TEMPLATE_NOTE")
            );
            return false;
        }

        Joomla\Utilities\ArrayHelper::toInteger($ids);

        if (!empty($ids) and $state == Prism\Constants::APPROVED) {

            $projects = $this->getProjectsData($ids);

            if (!$projects) {
                $this->log->add(
                    JText::sprintf("PLG_CONTENT_CROWDFUNDINGUSERMAIL_ERROR_INVALID_PROJECTS", $this->name),
                    "PLG_CONTENT_USERE_MAIL_ERROR",
                    JText::_("PLG_CONTENT_CROWDFUNDINGUSERMAIL_ERROR_INVALID_PROJECTS_NOTE")
                );
                return false;
            }

            foreach ($projects as $project) {

                // Send email to the administrator.
                $return = $this->sendMail($project, $emailId);

                // If there is an error, stop the loop.
                // Let the administrator to look the errors.
                if ($return !== true) {
                    return false;
                }

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

        $query->select("a.title, c.name, c.email");
        $query->select($query->concatenate(array("a.id", "a.alias"), ":") . " AS slug");
        $query->select($query->concatenate(array("b.id", "b.alias"), ":") . " AS catslug");

        $query
            ->from($db->quoteName("#__crowdf_projects", "a"))
            ->leftJoin($db->quoteName("#__categories", "b") . " ON a.catid = b.id")
            ->leftJoin($db->quoteName("#__users", "c") . " ON a.user_id = c.id")
            ->where("a.id IN (" . implode(",", $ids) . ")");

        $db->setQuery($query);
        $results = $db->loadObjectList();

        if (!$results) {
            $results = array();
        }

        return $results;
    }

    protected function sendMail($project, $emailId)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get website
        $uri     = JUri::getInstance();
        $website = $uri->toString(array("scheme", "host"));

        $emailMode = $this->params->get("email_mode", "plain");

        // Route project URI
        $appSite   = JApplicationCms::getInstance('site');
        $router    = $appSite->getRouter('site');

        $routedUri = $router->build(CrowdfundingHelperRoute::getDetailsRoute($project->slug, $project->catslug));
        if ($routedUri instanceof JUri) {
            $routedUri = $routedUri->toString();
        }

        if (0 === strpos($routedUri, "/administrator")) {
            $routedUri = str_replace("/administrator", "", $routedUri);
        }

        // Prepare data for parsing
        $data = array(
            "site_name"  => $app->get("sitename"),
            "site_url"   => JUri::root(),
            "item_title" => $project->title,
            "item_url"   => $website . $routedUri,
        );

        // Send mail to the administrator
        if (!$emailId) {
            return false;
        }

        $email = new EmailTemplates\Email();
        $email->setDb(JFactory::getDbo());
        $email->load($emailId);

        if (!$email->getSenderName()) {
            $email->setSenderName($app->get("fromname"));
        }
        if (!$email->getSenderEmail()) {
            $email->setSenderEmail($app->get("mailfrom"));
        }

        $recipientName = $project->name;
        $recipientMail = $project->email;

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
                JText::sprintf("PLG_CONTENT_CROWDFUNDINGUSERMAIL_ERROR_SEND_MAIL", $this->name),
                "PLG_CONTENT_USERE_MAIL_ERROR",
                JText::sprintf("PLG_CONTENT_CROWDFUNDINGUSERMAIL_ERROR_SEND_MAIL_NOTE", $mailer->ErrorInfo)
            );

            return false;
        }

        return true;
    }
}
