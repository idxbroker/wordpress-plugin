window.addEventListener("DOMContentLoaded", function(){
	// Hide extended fields if none are set
	if ( document.querySelector(".extended .left").childElementCount === 0 && document.querySelector(".extended .right").childElementCount	=== 0	) {
		document.querySelector(".extended").style.display = "none"
	}
	// Style tables
	var detailContainerList = ["core-fields", "extended", "advanced"]
	detailContainerList.forEach(function(element){
		var classString = "." + CSS.escape(element)
		var leftGroupElementCount = ( document.querySelector( classString + " tbody.left" ) ? document.querySelector( classString + " tbody.left" ).childElementCount : 0 )
		var rightGroupElementCount = ( document.querySelector( classString + " tbody.right") ? document.querySelector( classString + " tbody.right").childElementCount : 0 )
		var style = document.createElement("style")
		style.type = "text/css"
		if ( leftGroupElementCount > 0 && rightGroupElementCount > 0 ) {
			if ( rightGroupElementCount < leftGroupElementCount ) {
				style.innerHTML = "@media only screen and (min-width: 767px) {" + classString + " tbody.right tr" + "{border-bottom: solid 1px #3333;} } " + classString + " tbody.left {border-right: solid 1px #3333;}"	
			}
			if ( leftGroupElementCount < rightGroupElementCount ) {
				style.innerHTML = "@media only screen and (min-width: 767px) {" + classString + " tbody.left tr {border-bottom: solid 1px #3333;} } " + classString + " tbody.right {border-left: solid 1px #3333;}"
			}
			if ( rightGroupElementCount === leftGroupElementCount  ) {
				style.innerHTML = classString + " tbody.right {border-left: solid 1px #3333;}"
			}
			document.getElementsByTagName("head")[0].appendChild(style)
		}
	});
});
