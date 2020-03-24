// Create event listener for each notice
function initalizeEventListeners() {
	var notices = document.getElementsByClassName('idx-notice');
	Array.prototype.forEach.call(notices, 
		function(notice) {

		// Remove wp default behavior
		jQuery(".notice-dismiss").off();

		dismissEventListener(notice);
		}
	);

}

// Checks if button was clicked, then runes our ajax function
function dismissEventListener(notice) {
	notice.addEventListener(
		'click',
		function(e) {
			if(e.target.tagName === "BUTTON") {
				e.preventDefault();
				noticeAjax(notice, e.target);
			}
		},
		false
	);
}

// Tells the server which notice was dismissed then updates menu notification number
// and removes notification when completed
function noticeAjax(notice, button) {
	waitingForAjaxStyle(notice, button)
	var data = {
		'action': 'idx_dismissed',
		'name': notice.dataset.name,
		'_ajax_nonce': idxNoticeNonce, // Nonce is injected into script via WordPress
	};

	// ajaxurl is a global var provided by WordPress
	jQuery.post(ajaxurl, data, function() {
		// Run default remove notice script provided by WordPress
		restoreWordPressBehavior(notice);

		updateSidebarIcon();
	});
}

// Fades out the notice box, removes the dismiss button, and adds a loading spinner
function waitingForAjaxStyle(notice, button) {
	button.style.display = "none";
	notice.style.opacity = ".6";
	notice.classList.add("notice-ajax");
}

// https://github.com/WordPress/WordPress/blob/f6a37e7d39e2534d05b9e542045174498edfe536/wp-admin/js/common.js#L762
function restoreWordPressBehavior(el) {
	el = jQuery(el);
	el.fadeTo(100, 0, function() {
		el.slideUp(100, function() {
			el.remove()
		});
	});
}

// Update notice icon in the menu
function updateSidebarIcon() {
	var notificationIcon = document.getElementById("idx-menu-notice-item");
	var notificationNum = +notificationIcon.innerHTML;
	if(notificationNum <= 1) {
		notificationIcon.style.display = "none";
	} else {
		notificationIcon.innerHTML = notificationNum - 1;
	}
}

// Wait to apply script until DOM is loaded so we can remove the WordPress notice event handler
document.addEventListener("DOMContentLoaded", function(event) {
	initalizeEventListeners();
});
