# Extbase modifications for Seminars TYPO3 extension

This is a modified version of the seminars extension:  
- new Plugin: Seminar Manager(Extbase) -> pi3 Plugin
- Extbase Controller: Event, Category
- Fluid Templates can be used and overwritten as usual (see pi3 in Configuration/TypoScript/setup.txt)
- removed mk_forms dependency, enabled EXT:form

All original EXT:seminars functions and templates are still available with the Plugin "Seminar Manager". 
Compatible to version: 2.1.2 
 
**mk_forms** is no longer required, you have to install dependencies by yourself, if you still want to use it   

## Current status: 
- not yet merged into EXT:seminars
- based on EXT:seminars 2.1.2  

## Using EXT:form for booking

For booking with EXT:form this is a example setup: 

1) add booking link to your template: 
   
       <f:link.page pageUid="123" additionalParams="{tx_form_formframework: {event: event.uid}}" >
          Booking now!
       </f:link.page>
 
2) add Event Viewhelper to the Fluid template, this will load all event data:
 
       <s:event loadParam="event" extensionKey="tx_form_formframework" />
       
   Attention: Maybe you have to pass the {event} data to the partials. You have to modify arguments of <f:render partial>     
 
3) Add the event data to your form

Here are more possible solutions, I added a hidden field:    

- Overwrite Hidden.html Partial: 
    
        <f:switch expression="{element.identifier}">
            <f:case value="event">
                <f:form.hidden
                        property="{element.identifier}"
                        id="{element.uniqueIdentifier}"
                        additionalAttributes="{formvh:translateElementProperty(element: element, property: 'fluidAdditionalAttributes')}"
                        value="{event.uid}" />
            </f:case>
            <f:defaultCase>
                <f:form.hidden
                        property="{element.identifier}"
                        id="{element.uniqueIdentifier}"
                        additionalAttributes="{formvh:translateElementProperty(element: element, property: 'fluidAdditionalAttributes')}"
                />
            </f:defaultCase>
        </f:switch>

Add hidden Fields to Form yaml: 

        renderables:
          -
            defaultValue: ''
            type: Hidden
            identifier: event
            label: 'Event ID'
            renderingOptions:
              _isHiddenFormElement: false
        



# Seminars TYPO3 extension

[![License](https://poser.pugx.org/oliverklee/seminars/license.svg)](https://packagist.org/packages/oliverklee/seminars)

This TYPO3 extension allows you to create and manage a list of seminars,
workshops, lectures, theater performances and other events, allowing front-end
users to sign up. FE users also can create and edit events.

Most of the documentation is in ReST format
[in the Documentation/ folder](Documentation/) and is rendered
[as part of the TYPO3 documentation](https://docs.typo3.org/typo3cms/extensions/seminars/).
