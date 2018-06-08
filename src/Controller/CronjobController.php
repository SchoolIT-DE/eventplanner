<?php

namespace App\Controller;

use SchoolIT\CommonBundle\Controller\AbstractCronjobController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class CronjobController extends AbstractCronjobController {

    /**
     * @Route("/cron/mails/send")
     */
    public function sendMails() {
        $this->denyAccessUnlessGranted('ROLE_CRON');

        return $this->runCommand([
            'command' => 'swiftmailer:spool:send',
            '--message-limit' => $this->getParameter('email_message_limit')
        ]);
    }
}