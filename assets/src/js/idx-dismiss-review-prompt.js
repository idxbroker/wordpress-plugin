window.addEventListener('DOMContentLoaded', function(){
    document.querySelector('.idx_accept_review_prompt').addEventListener('click',
        function(event){dismissPrompt(event, false)}
    );
    document.querySelector('.idx_dismiss_review_prompt').addEventListener('click', 
        function(event){dismissPrompt(event, true)}
    );
    function dismissPrompt(event, preventDefault){
        if(preventDefault){
            event.preventDefault();
        }
        jQuery.post(
            ajaxurl, {
                'action': 'idx_dismiss_review_prompt'
            }).done(function(){return location.reload();});
    }
});
