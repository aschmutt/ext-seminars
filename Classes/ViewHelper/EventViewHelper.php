<?php
namespace OliverKlee\Seminars\ViewHelper;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Event viewhelper - loads Event object into Fluid template
 *
 * = Basic usage =
 * Load Event by passing eventUid
 *
 * <code title="Example">
 *      <s:event eventUid="123" />
 * </code>
 *
 * = usage in another extension =
 *
 * For usage in another extension, you can pass Event parameter via GET or POST.
 * In this example event is loaded in EXT:form
 *
 * <code title="Link to booking Form">
 * <f:link.page pageUid="123" additionalParams="{tx_form_formframework: {event: event.uid}}" >
 *       Booking now!
 *  </f:link.page>
 * </code>
 *
 * <code title="Form Template">
 *  <s:event loadParam="event" extensionKey="tx_form_formframework" />
 * </code>
 *
 */
class EventViewHelper extends AbstractViewHelper {

    /**
    * Loads Event Object into Fluid Template
    *
    * @param string $loadParam name of GET/POST Parameter containing the uid (prio 1)
    * @param string $extensionKey - Extension Prefix for TYPO3 Links, like: tx_form_formframework
    * @param int $eventUid load event with this uid (prio 2)
    *
    * @return string empty string
    */
    public function render($eventUid=0, $loadParam="", $extensionKey="") {

        $eventUid = (int)$eventUid;

        if (strlen($loadParam) > 0) {
            if (strlen($extensionKey) > 0) {
                $paramArray = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP($extensionKey);
                if (array_key_exists($loadParam,$paramArray)) {
                    $param = $paramArray[$loadParam];
                }
            } else {
                $param = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP($loadParam);
            }

            if ((int)$param > 0) {
                $eventUid = (int)$param;
            }
        }

        if ($eventUid > 0) {
            $eventRepository = $this->objectManager->get('Tx_Seminars_Mapper_Event');
            $event = $eventRepository->find($eventUid);
            $this->templateVariableContainer->add('event',$event);
        }

        return '';
    }

}
