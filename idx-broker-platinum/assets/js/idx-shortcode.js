
document.addEventListener('DOMContentLoaded', function(event){

    var modal = el('#idx-shortcode-modal')[0];
    var close = modal.querySelector('.media-modal-close');
    var overlay = el('#idx-overlay')[0];
    var innerContent = el('.idx-modal-inner-content')[0];
    var overView = el('.idx-modal-inner-overview')[0];
    var editTab = el('.idx-modal-shortcode-edit')[0];
    var insertButton = el('.idx-toolbar-primary button')[0];
    var modalTitle = el('#idx-shortcode-modal h1')[0];
    var previewTab = el('.idx-modal-shortcode-preview')[0];
    var editTabButton = el('.idx-modal-tabs a:nth-of-type(1)')[0];
    var previewTabButton = el('.idx-modal-tabs a:nth-of-type(2)')[0];
    var tabButtons = el('.idx-modal-tabs-router')[0];


    //helper function avoiding jQuery for speed
    function el(selector){
        return document.querySelectorAll(selector);
    }

    //helper function for loops
    function forEach (array, callback, scope) {
      for (var i = 0; i < array.length; i++) {
        callback.call(scope, array[i], i);
      }
    };

/*
 * Modal UI and Initializing
 *
 */

    //Open the Modal and perform necessary actions
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
    }

    //Close the modal and perform reset actions in case they open it again
    function closeShortcodeModal(event) {
        //only close the modal if the overlay, close, or insert buttons are clicked
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
        }
    }


    //initialize button and modal functionality
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

    //get options for the shortcode type selected before insertion
    function getShortcodeData(event){
        var nodeName = event.target.nodeName;
        //prevent bug where clicking on the icon does not give correct shortname attribute
        if(nodeName === 'I'){
            shortcodeType = event.target.parentNode.parentNode.getAttribute('data-short-name');
        } else{
            shortcodeType = event.target.parentNode.getAttribute('data-short-name');
        }
        overView.style.display = 'none';
        editTab.style.display = 'block';
        //Display Loading Icon while Options Load
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

    //Change Details Modal Title
    function shortcodeDetailTitle(shortcodeType){
        if(shortcodeType === 'system_links'){
                modalTitle.innerHTML = 'IDX Shortcode Details - System Links';
        } else if(shortcodeType === 'saved_links'){
                modalTitle.innerHTML = 'IDX Shortcode Details - Saved Links';
        } else if(shortcodeType === 'widgets'){
                modalTitle.innerHTML = 'IDX Shortcode Details - Widgets';
        } else if(shortcodeType === 'omnibar'){
                modalTitle.innerHTML = 'IDX Shortcode Preview - Omnibar';
        } else if(shortcodeType === 'omnibar_extra'){
                modalTitle.innerHTML = 'IDX Shortcode Preview - Omnibar With Extra Fields';
        } else {
            //for a custom third party title
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

        //omnibar uses photo instead, so do not load content in that case
        if(loadContent !== false){
            jQuery.post(
            ajaxurl, {
                'action': 'idx_shortcode_preview',
                'idx_shortcode' : shortcode
            }).done(function(data){
                //set the preview tab to active styling
                el('.idx-modal-tabs a:nth-of-type(2)')[0].classList.add('idx-active-tab');
                //fill the preview tab with shortcode data
                previewTab.innerHTML = data;
                //evaluate scripts that would not load otherwise
                var scripts = previewTab.querySelectorAll('script');
                return evalScripts(scripts);
            }).fail(function(data){
                //if shortcode content fails, go back to the edit tab
                openEditTab(event);
            });
        }
    }

    //evaluate scripts - both external and inline
    function evalScripts(scripts){
        if(typeof scripts[0] === 'undefined'){
            return;
        }
        forEach(scripts, function(value){
            //if external script, load it otherwise evaluate it
            var src = value.getAttribute('src');
            if(src !== null){
                return jQuery.getScript(src);
            } else {
                eval(value.innerHTML);
            }
        })
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
        if(typeof input !== 'undefined'){
            return input;
        } else if(typeof select !== 'undefined'){
            return select;
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










