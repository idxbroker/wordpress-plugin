
document.addEventListener('DOMContentLoaded', function(event){

    var modal = el('#idx-shortcode-modal')[0];
    var close = modal.querySelector('.media-modal-close');
    var overlay = el('#idx-overlay')[0];
    var innerContent = el('.idx-modal-inner-content')[0];
    var overView = el('.idx-modal-inner-overview')[0];
    var editOptions = el('.idx-modal-shortcode-edit')[0];
    var insertButton = el('.idx-toolbar-primary button')[0];
    var modalTitle = el('#idx-shortcode-modal h1')[0];






    function el(selector){
        return document.querySelectorAll(selector);
    }

    function forEach (array, callback, scope) {
      for (var i = 0; i < array.length; i++) {
        callback.call(scope, array[i], i);
      }
    };


    function openShortcodeModal(event) {
        event.preventDefault();
        modal.style.display = 'block';
        overlay.style.display = 'block';
        el('body')[0].style.overflow = 'hidden';
        el('#wpbody')[0].style.zIndex = '160000';
        editOptions.style.display = 'none';
        insertButton.setAttribute('disabled', 'disabled');
    }

    function closeShortcodeModal(event) {
        event.preventDefault();
        modal.style.display = 'none';
        overlay.style.display = 'none';
        el('body')[0].style.overflow = 'initial';
        el('#wpbody')[0].style.zIndex = 'initial';
        overView.style.display = 'block';
        editOptions.innerHTML = '';
        modalTitle.innerHTML = 'Insert IDX Shortcode';
    }


    //initialize button and modal functionality
    function initializeModal(){
        el('#idx-shortcode')[0].addEventListener('click', openShortcodeModal);
        overlay.addEventListener('click', closeShortcodeModal);
        close.addEventListener('click', closeShortcodeModal);
        makeTypesSelectable();
        insertButton.addEventListener('click', insertShortcode);
    }

    function makeTypesSelectable(){
        forEach(el('.idx-shortcode-type'), function (value, index) {value.addEventListener('click', getShortcodeData);});
    }

    function getShortcodeData(event){
        shortcodeType = event.target.parentNode.getAttribute('data-short-name');
        jQuery.post(
            ajaxurl, {
                'action': 'idx_shortcode_options',
                'idx_shortcode_type' : shortcodeType
            }).done(function(data){
                editOptions.innerHTML = data;
                var select = editOptions.querySelector('select');
                jQuery(select).select2();
                insertButton.removeAttribute('disabled');
                overView.style.display = 'none';
                editOptions.style.display = 'block';
                updateTitle();
                jQuery(select).on('change', updateTitle);
            }).fail(function(data){
                getShortcodeData(event);
        });
    }

    function shortcodeDetailTitle(shortcodeType){
        if(shortcodeType === 'system_link'){
                modalTitle.innerHTML = 'IDX Shortcode Details - System Links';
        } else if(shortcodeType === 'saved_link'){
                modalTitle.innerHTML = 'IDX Shortcode Details - Saved Links';
        } else if(shortcodeType === 'widgets'){
                modalTitle.innerHTML = 'IDX Shortcode Details - Widgets';
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

    function formShortcode(){
        var subtype = el('#idx-select-subtype')[0];
        var shortcodeType = subtype.value;
        var selected = findSelected('#idx-select-subtype option');
        var id = "id=\"" + selected.getAttribute('id') + "\"";
        var title = formTitle(selected);
        var shortcode = '[' + shortcodeType + ' ' + id + ' ' + title + ']';
        return shortcode;
    }

    function formTitle(selectedOption){
        var titleInput = el('.idx-modal-shortcode-edit #title')[0];
        if(typeof titleInput !== 'undefined' && titleInput.value !== ''){
            return 'title=\"' + titleInput.value + "\"";
        } else if(typeof titleInput === 'undefined') {
            return '';
        } else {
            var selected = findSelected('#idx-select-subtype option');
            if(selected){
                title = selected.innerHTML;
            }
            return 'title=\"' + title + "\"";
        }
    }

    function updateTitle(){
        var titleInput = el('.idx-modal-shortcode-edit #title')[0];
        if(typeof titleInput !== 'undefined'){
            titleInput.value = findSelected('#idx-select-subtype option').innerHTML;
        }
    }

    function findSelected(query){
        var selected = false;
        forEach(el(query), function(value){
                if(value.selected){
                    return selected = value;
                }
            });
        return selected;
    }

    function insertShortcode(event){
        event.preventDefault();
        shortcode = formShortcode();
        send_to_editor(shortcode);
        closeShortcodeModal(event);
    }




    initializeModal();





});










