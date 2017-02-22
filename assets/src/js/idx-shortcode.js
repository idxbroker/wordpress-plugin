document.addEventListener('DOMContentLoaded', function(event){

    if(typeof el('#idx-shortcode')[0] === 'undefined'){
        return;
    }

    var modal = el('#idx-shortcode-modal')[0];
    var close = modal.querySelector('.media-modal-close');
    var overlay = el('#idx-overlay')[0];
    var innerContent = el('.idx-modal-inner-content')[0];
    var overView = el('.idx-modal-inner-overview')[0];
    var editTab = el('.idx-modal-shortcode-edit')[0];
    var insertButton = el('.idx-toolbar-primary button')[0];
    var modalTitle = el('#idx-shortcode-modal h1')[0];
    var backButton = el('.idx-back-button a')[0];
    var previewTab = el('.idx-modal-shortcode-preview')[0];
    var editTabButton = el('.idx-modal-tabs a:nth-of-type(1)')[0];
    var previewTabButton = el('.idx-modal-tabs a:nth-of-type(2)')[0];
    var tabButtons = el('.idx-modal-tabs-router')[0];
    var styleSheetUrls = [];
    var previewScriptsLoaded = false;


    //Helper function avoiding jQuery for speed.
    function el(selector){
        return document.querySelectorAll(selector);
    }

    //Helper function for loops.
    function forEach (array, callback, scope) {
      for (var i = 0; i < array.length; i++) {
        callback.call(scope, array[i], i);
      }
    };

/*
 * Modal UI and Initializing
 *
 */

    //Open the Modal and perform necessary actions.
    function openShortcodeModal(event) {
        event.preventDefault();
        modal.style.display = 'block';
        overlay.style.display = 'block';
        el('body')[0].style.overflow = 'hidden';
        el('#wpbody')[0].style.zIndex = '160000';
        editTab.style.display = 'none';
        insertButton.setAttribute('disabled', 'disabled');
        previewTabButton.addEventListener('click', openPreviewTab);
        editTabButton.addEventListener('click', openEditTab);
        backButton.addEventListener('click', backToOverview);
        el('.idx-back-button')[0].style.display = 'none';
    }

    //Close the modal and perform reset actions in case they open it again.
    function closeShortcodeModal(event) {
        //Only close the modal if the overlay, close, or insert buttons are clicked.
        if(event.target === modal || event.target === close || event.target === close.querySelector('span') || event.target === insertButton){
            event.preventDefault();
            modal.style.display = 'none';
            overlay.style.display = 'none';
            el('body')[0].style.overflow = 'initial';
            el('#wpbody')[0].style.zIndex = 'initial';
            overView.style.display = 'block';
            editTab.innerHTML = '';
            tabButtons.style.display = 'none';
            previewTab.style.display = 'none';
            previewTabButton.classList.remove('idx-active-tab');
            editTabButton.classList.add('idx-active-tab');
            modalTitle.innerHTML = 'Insert IDX Shortcode';
            previewTabButton.removeEventListener('click', openPreviewTab);
            editTabButton.removeEventListener('click', openEditTab);
            backButton.removeEventListener('click', backToOverview);
        }
    }

    //Back button to get from Shortcode Details to the Overview.
    function backToOverview(event){
        event.preventDefault();
        overView.style.display = 'block';
        editTab.innerHTML = '';
        tabButtons.style.display = 'none';
        previewTab.style.display = 'none';
        editTab.style.display = 'none';
        insertButton.setAttribute('disabled', 'disabled');
        previewTabButton.classList.remove('idx-active-tab');
        editTabButton.classList.add('idx-active-tab');
        modalTitle.innerHTML = 'Insert IDX Shortcode';
        el('.idx-back-button')[0].style.display = 'none';
    }


    //Initialize button and modal functionality.
    function initializeModal(){
        el('#idx-shortcode')[0].addEventListener('click', openShortcodeModal);
        modal.addEventListener('click', function(event){
            closeShortcodeModal(event);
        });
        close.addEventListener('click', closeShortcodeModal);
        makeTypesSelectable();
        insertButton.addEventListener('click', insertShortcode);
    }

    //Initialize type buttons being clickable. When clicking them, it loads the options.
    function makeTypesSelectable(){
        forEach(el('.idx-shortcode-type'), function (value, index) {value.addEventListener('click', getShortcodeData);});
    }

    //Get options for the shortcode type selected before insertion.
    function getShortcodeData(event){
        var nodeName = event.target.nodeName;
        //Prevent bug where clicking on the icon does not give correct shortname attribute.
        if(nodeName === 'I'){
            shortcodeType = event.target.parentNode.parentNode.getAttribute('data-short-name');
        } else{
            shortcodeType = event.target.parentNode.getAttribute('data-short-name');
        }
        overView.style.display = 'none';
        editTab.style.display = 'block';
        el('.idx-back-button')[0].style.display = 'block';
        //Display Loading Icon while Options Load.
        editTab.innerHTML = "<div class=\"idx-loader\"></div>";
        return jQuery.post(
            ajaxurl, {
                'action': 'idx_shortcode_options',
                'idx_shortcode_type' : shortcodeType
            }).done(function(data){
                editTab.innerHTML = data;
                var select = editTab.querySelector('select');
                jQuery(select).select2();
                insertButton.removeAttribute('disabled');
                shortcodeDetailTitle(shortcodeType);
                updateTitle();
                jQuery(select).on('change', updateTitle);
                tabButtons.style.display = 'inline-block';
                var scripts = editTab.querySelectorAll('script');
                evalScripts(scripts);
            }).fail(function(data){
                getShortcodeData(event);
        });
    }

    //Change Details Modal Title.
    function shortcodeDetailTitle(shortcodeType){
        switch(shortcodeType){
            case 'system_links':
                modalTitle.innerHTML = 'Shortcode Details - System Links';
                break;
            case 'saved_links':
                modalTitle.innerHTML = 'Shortcode Details - Saved Links';
                break;
            case 'widgets':
                modalTitle.innerHTML = 'Shortcode Details - Widgets';
                break;
            case 'omnibar':
                modalTitle.innerHTML = 'Shortcode Details - IMPress Omnibar';
                break;
            case 'impress_lead_login':
                modalTitle.innerHTML = 'Shortcode Details - IMPress Lead Login';
                break;
            case 'impress_lead_signup':
                modalTitle.innerHTML = 'Shortcode Details - IMPress Lead Signup';
                break;
            case 'impress_city_links':
                modalTitle.innerHTML = 'Shortcode Details - IMPress City Links';
                break;
            case 'impress_property_showcase':
                modalTitle.innerHTML = 'Shortcode Details - IMPress Property Showcase';
                break;
            case 'impress_property_carousel':
                modalTitle.innerHTML = 'Shortcode Details - IMPress Property Carousel';
                break;
            case 'idx_wrapper_tags':
                modalTitle.innerHTML = 'Shortcode Details - IDX Wrapper Tags';
                break;
            default:
                //For a custom third party title.
                jQuery.post(
                ajaxurl, {
                    'action': 'idx_shortcode_title',
                    'idx_shortcode_type' : shortcodeType
                }).done(function(data){
                    modalTitle.innerHTML = data;
                }).fail(function(data){
                    modalTitle.innerHTML = 'Shortcode Details - ' + shortcodeType;
                });
        }
    }

    //Load shortcode html via ajax.
    function openPreviewTab(event, loadContent){
        event.preventDefault();
        var shortcode = formShortcode();
        var fields = el('.idx-modal-shortcode-field');
        var shortcodeType = fields[0].getAttribute('data-shortcode');
        editTab.style.display = 'none';
        previewTab.style.display = 'block';
        previewTab.innerHTML = '<div class=\"idx-loader\"></div>';
        editTabButton.classList.remove('idx-active-tab');
        previewTabButton.classList.add('idx-active-tab');

        //Omnibar uses photo instead, so do not load content in that case.
        if(loadContent !== false){
            jQuery.post(
            ajaxurl, {
                'action': 'idx_shortcode_preview',
                'idx_shortcode' : shortcode
            }).done(function(data){
                previewScriptsLoaded = false;
                previewTab.querySelector('.idx-loader').style.display = 'none';
                //Set the preview tab to active styling.
                el('.idx-modal-tabs a:nth-of-type(2)')[0].classList.add('idx-active-tab');
                //Fill the preview tab with shortcode data.
                loadIframe(data);
                var iframe = previewTab.querySelector('iframe');
                //Load scripts on edittab into iframe preview.
                evalPreviewScripts(editTab.querySelectorAll('script'), iframe.contentWindow.document.body);
                //Load scripts in shortcode into iframe.
                return; 
            }).fail(function(data){
                //If shortcode content fails, go back to the edit tab.
                openEditTab(event);
            });
        }
    }

    function loadIframe(data){
        var iframe = document.createElement('iframe');
        iframe.style.width = '100%';
        iframe.style.minHeight = '100%';
        iframe.onload = function(){
            iframe.contentWindow.document.body.innerHTML = '<div class="idx-modal-shortcode-preview">' + data + '</div>';
            addStyleSheets(styleSheetUrls, iframe.contentWindow.document.body);
        }
        previewTab.appendChild(iframe);
    }

    //evaluate scripts - both external and inline
    function evalPreviewScripts(scripts, destination){
        if(typeof scripts[0] === 'undefined'){
            return loadPreviewScripts();
        }

        var i = 0;

        loadScript(scripts[i]);

        function loadScript(script){
            //if external script, load it otherwise evaluate it
            var src = script.getAttribute('src');
            var dynamic = script.getAttribute('data-dynamic');
            //index
            i++;
            //if script was added by this function, do not load again
            if(dynamic !== null){
                if(scripts[i]){
                    return loadScript(scripts[i]);
                } else {
                    return loadPreviewScripts();
                }
            }
            //if external script bring it in. Otherwise evaluate it.
            if(src !== null){
                var newScript = document.createElement('script');
                newScript.src = src;
                newScript.setAttribute('data-dynamic', true);


                //if another script exists, load it when this one is done (synchronously)
                if(scripts[i]){
                    newScript.onload = function(){
                        loadScript(scripts[i]);
                    }
                } else {
                    newScript.onload = function(){
                        loadPreviewScripts();
                    }
                }

                return destination.appendChild(newScript);
            } else {
                var iframe = previewTab.querySelector('iframe');
                iframe.contentWindow.eval(script.innerHTML);
                //if another script exists, load it after this one
                if(scripts[i]){
                    loadScript(scripts[i]);
                } else {
                    //otherwise load preview scripts;
                    return loadPreviewScripts();
                }
            }
        }
    }

    function evalScripts(scripts){
        if(scripts[0] === 'undefined'){
            return;
        }
        forEach(scripts, function(value){
            eval(value.innerHTML);
        })
    }

    function loadPreviewScripts(){
        if(previewScriptsLoaded){
            return;
        }
        previewScriptsLoaded = true;
        var iframe = previewTab.querySelector('iframe');
        evalPreviewScripts(iframe.contentWindow.document.querySelectorAll('script'), iframe.contentWindow.document.body);
    }

    function addStyleSheets(urls, context){
        //only load styles when default styles is checked
        var styles = document.querySelectorAll('.idx-modal-shortcode-edit #styles')[0];

        if(typeof styles !== 'undefined' && styles.checked){
            forEach(urls, function(value){
                addStyleSheet(value, context);
            });
        }
    }

    //Add a stylesheet for shortcodes to be styled properly
    function addStyleSheet(url, context){
        var styleSheet = document.createElement("link");
        styleSheet.setAttribute("rel", "stylesheet");
        styleSheet.setAttribute("type", "text/css");
        styleSheet.setAttribute("href", url);
        if(typeof removable !== 'undefined'){
            styleSheet.setAttribute("class", "preview-css");
        }
        return context.appendChild(styleSheet);
    }


    //Go back to the edit tab after a preview of the shortcode
    function openEditTab(event){
        event.preventDefault();
        previewTab.style.display = 'none';
        editTab.style.display = 'block';
        previewTabButton.classList.remove('idx-active-tab');
        editTabButton.classList.add('idx-active-tab');
    }

/*
 * Forming and Inserting the Shortcode
 *
 */

    //Grab Elements and form the shortcode for insertion
    function formShortcode(){
        var fields = el('.idx-modal-shortcode-field');
        var shortcodeType = fields[0].getAttribute('data-shortcode');
        fieldList = getFieldNames(fields);
        extraParameters = formExtraParameters(fieldList);
        var shortcode = '[' + shortcodeType + extraParameters + ']';
        return shortcode;
    }

    //Create an array of field names and values for forming the shortcode
    function getFieldNames(fields){
        var fieldList = [];
        forEach(fields, function(value){
            var input = getInput(value);
            var field = {};
            if(!input){
                return '';
            }
            //the field name is under data-short-name
            field.name = input.getAttribute('data-short-name');
            field.value = input.value;
            if(field.name === 'title'){
                field.value = formTitle(field.value);
            }
            fieldList.push(field);
        });
        return fieldList;
    }

    //Find if the user has entered a title. If not, use the default.
    function formTitle(input){
        if(typeof input !== 'undefined' && input !== ''){
            return input;
        } else {
            var selected = el('#idx-select-subtype option')[0];
            if(typeof selected !== 'undefined'){
                title = findSelected('#idx-select-subtype option').innerHTML;
            } else {
                title = '';
            }
            return title;
        }
    }
    //Find the select or input within the field div and return it
    function getInput(field){
        var input = field.querySelectorAll('input')[0];
        var select = field.querySelectorAll('select')[0];
        var textarea = field.querySelectorAll('textarea')[0];
        var checkbox = field.querySelectorAll('input[type=checkbox]')[0];
        if(typeof checkbox !== 'undefined'){
            if(checkbox.checked){
                checkbox.value = 1;
            } else{
                checkbox.value = 0;
            }
            return checkbox;
        }
        if(typeof input !== 'undefined'){
            return input;
        } else if(typeof select !== 'undefined'){
            return select;
        } else if(typeof textarea !== 'undefined'){
            return textarea;
        }
        return false;
    }

    //Use the getFieldNames object and create a string for the field names and values for shortcode forming
    function formExtraParameters(fieldList){
        if(fieldList === ''){
            return '';
        }
        var extraParameters = ' ';
        forEach(fieldList, function(value){
            extraParameters += value.name + '="' + value.value + '" ';
        });
        return extraParameters;
    }

    //Update the title field with the default title
    function updateTitle(){
        var titleInput = el('.idx-modal-shortcode-edit #title')[0];
        if(typeof titleInput !== 'undefined'){
            titleInput.value = findSelected('#idx-select-subtype option').innerHTML;
        }
    }

    //Find the selected option from a select
    function findSelected(query){
        var selected = false;
        forEach(el(query), function(value){
                if(value.selected){
                    return selected = value;
                }
            });
        return selected;
    }

    //insert the shortcode in the editor and close the modal
    function insertShortcode(event){
        event.preventDefault();
        shortcode = formShortcode();
        send_to_editor(shortcode);
        closeShortcodeModal(event);
    }

    //Initialize the modal
    initializeModal();

});