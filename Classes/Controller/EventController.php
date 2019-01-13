<?php
namespace OliverKlee\Seminars\Controller;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * This file is part of the "seminars" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * @author Andrea Schmuttermair <andrea@schmutt.de>
 */


/**
 * Controller of news records
 *
 */
class EventController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \Tx_Seminars_Mapper_Event
     */
    protected $eventRepository;
    /**
     * @param \Tx_Seminars_Mapper_Event $eventRepository
     */
    public function injectEventRepository(\Tx_Seminars_Mapper_Event $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function initializeAction() {
       if (isset($this->settings['flexforms']) && is_array($this->settings['flexforms'])) {
            $flexformsSettings = $this->settings['flexforms'];
            //unset($this->settings['flexforms']);
            foreach ($flexformsSettings as $flexformKey => $flexformValue){
                if ((strlen($flexformValue) > 0 || (int)$flexformValue > 0)) {
                    $this->settings[$flexformKey] = $flexformValue;
                }
            }
       }
    }


    /**
     * @var \Tx_Seminars_FrontEnd_DefaultController
     * @inject
     */
    protected $frontendController;



    /**
     * action list
     *
     * @return void
     */
    public function listAction() {

        $events = $this->eventRepository->findBySettings($this->settings);

        $events = $this->eventRepository->addDetailUri(
            $events,
            $this->settings,
            $this->controllerContext->getUriBuilder()
        );

        $this->view->assign('events', $events);

    }

    /**
     * action show
     *
     * @param int $eventUid
     *
     * @return void
     */
    public function showAction($eventUid = 0) {

        if ((int)$this->settings['showSingleEvent'] > 0) {
            $event = $this->eventRepository->find((int)$this->settings['showSingleEvent']);
            $this->view->assign('event',$event);
        } else if ($eventUid > 0) {
            //@todo: if eventUid not exists, exceptions is thrown, catch does not work?
            try {
                $event = $this->eventRepository->find($eventUid);
            } catch (\Exception $e) {
                DebuggerUtility::var_dump($e);
            }

            $this->view->assign('event',$event);
        }

    }









}
