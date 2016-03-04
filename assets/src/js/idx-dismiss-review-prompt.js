window.addEventListener('DOMContentLoaded', function(){
    // Review button
    document.querySelector('.idx_accept_review_prompt').addEventListener('click',
        function(event){dismissPrompt(event, false)}
    );
    // no thanks button
    document.querySelector('.idx_dismiss_review_prompt').addEventListener('click', 
        function(event){dismissPrompt(event, true)}
    );
    //x button at top right
    document.querySelector('.idx_review_prompt .notice-dismiss').addEventListener('click',
        function(event){dismissPrompt(event, true)}
    );
    function dismissPrompt(event, preventDefault){
        //keep link from navigating away from page - true or false
        if(preventDefault){
            event.preventDefault();
        }
        //immediately hide notification then disable permanently via ajax
        document.querySelector('.idx_review_prompt').style.display='none';
        jQuery.post(
            ajaxurl, {
                'action': 'idx_dismiss_review_prompt'
            });
    }
});
