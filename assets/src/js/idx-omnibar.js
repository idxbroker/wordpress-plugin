// Awesomplete - Lea Verou - MIT license
!function(){function t(t,e){for(var i in t){var n=t[i],s=this.input.getAttribute("data-"+i.toLowerCase());this[i]="number"==typeof n?parseInt(s):n===!1?null!==s:n instanceof Function?null:s,this[i]||0===this[i]||(this[i]=i in e?e[i]:n)}}function e(t,e){return"string"==typeof t?(e||document).querySelector(t):t||null}function i(t,e){return r.call((e||document).querySelectorAll(t))}function n(){i("input.awesomplete").forEach(function(t){new Awesomplete(t)})}var s=function(i,n){var r=this;this.input=e(i),this.input.setAttribute("autocomplete","off"),this.input.setAttribute("aria-autocomplete","list"),n=n||{},t.call(this,{minChars:2,maxItems:10,autoFirst:!1,filter:s.FILTER_CONTAINS,sort:s.SORT_BYLENGTH,item:function(t,i){return e.create("li",{innerHTML:t.replace(RegExp(e.regExpEscape(i.trim()),"gi"),"<mark>$&</mark>"),"aria-selected":"false"})},replace:function(t){this.input.value=t}},n),this.index=-1,this.container=i.parentNode.className.search(/awesomplete/)<0?e.create("div",{className:"awesomplete",around:i}):i.parentNode,this.ul=e.create("ul",{hidden:"",inside:this.container}),this.status=e.create("span",{className:"visually-hidden",role:"status","aria-live":"assertive","aria-relevant":"additions",inside:this.container}),e.bind(this.input,{input:this.evaluate.bind(this),blur:this.close.bind(this),keydown:function(t){var e=t.keyCode;r.opened&&(13===e&&r.selected?(t.preventDefault(),r.select()):27===e?r.close():(38===e||40===e)&&(t.preventDefault(),r[38===e?"previous":"next"]()))}}),e.bind(this.input.form,{submit:this.close.bind(this)}),e.bind(this.ul,{mousedown:function(t){var e=t.target;if(e!==this){for(;e&&!/li/i.test(e.nodeName);)e=e.parentNode;e&&r.select(e)}}}),this.input.hasAttribute("list")?(this.list="#"+i.getAttribute("list"),i.removeAttribute("list")):this.list=this.input.getAttribute("data-list")||n.list||[],s.all.push(this)};s.prototype={set list(t){Array.isArray(t)?this._list=t:"string"==typeof t&&t.indexOf(",")>-1?this._list=t.split(/\s*,\s*/):(t=e(t),t&&t.children&&(this._list=r.apply(t.children).map(function(t){return t.textContent.trim()}))),document.activeElement===this.input&&this.evaluate()},get selected(){return this.index>-1},get opened(){return this.ul&&null==this.ul.getAttribute("hidden")},close:function(){this.ul.setAttribute("hidden",""),this.index=-1,e.fire(this.input,"awesomplete-close")},open:function(){this.ul.removeAttribute("hidden"),this.autoFirst&&-1===this.index&&this.goto(0),e.fire(this.input,"awesomplete-open")},next:function(){var t=this.ul.children.length;this.goto(this.index<t-1?this.index+1:-1)},previous:function(){var t=this.ul.children.length;this.goto(this.selected?this.index-1:t-1)},"goto":function(t){var i=this.ul.children;this.selected&&i[this.index].setAttribute("aria-selected","false"),this.index=t,t>-1&&i.length>0&&(i[t].setAttribute("aria-selected","true"),this.status.textContent=i[t].textContent),e.fire(this.input,"awesomplete-highlight")},select:function(t){if(t=t||this.ul.children[this.index]){var i;e.fire(this.input,"awesomplete-select",{text:t.textContent,preventDefault:function(){i=!0}}),i||(this.replace(t.textContent),this.close(),e.fire(this.input,"awesomplete-selectcomplete"))}},evaluate:function(){var t=this,e=this.input.value;e.length>=this.minChars&&this._list.length>0?(this.index=-1,this.ul.innerHTML="",this._list.filter(function(i){return t.filter(i,e)}).sort(this.sort).every(function(i,n){return t.ul.appendChild(t.item(i,e)),n<t.maxItems-1}),0===this.ul.children.length?this.close():this.open()):this.close()}},s.all=[],s.FILTER_CONTAINS=function(t,i){return RegExp(e.regExpEscape(i.trim()),"i").test(t)},s.FILTER_STARTSWITH=function(t,i){return RegExp("^"+e.regExpEscape(i.trim()),"i").test(t)},s.SORT_BYLENGTH=function(t,e){return t.length!==e.length?t.length-e.length:e>t?-1:1};var r=Array.prototype.slice;return e.create=function(t,i){var n=document.createElement(t);for(var s in i){var r=i[s];if("inside"===s)e(r).appendChild(n);else if("around"===s){var o=e(r);o.parentNode.insertBefore(n,o),n.appendChild(o)}else s in n?n[s]=r:n.setAttribute(s,r)}return n},e.bind=function(t,e){if(t)for(var i in e){var n=e[i];i.split(/\s+/).forEach(function(e){t.addEventListener(e,n)})}},e.fire=function(t,e,i){var n=document.createEvent("HTMLEvents");n.initEvent(e,!0,!0);for(var s in i)n[s]=i[s];t.dispatchEvent(n)},e.regExpEscape=function(t){return t.replace(/[-\\^$*+?.()|[\]{}]/g,"\\$&")},"undefined"!=typeof Document&&("loading"!==document.readyState?n():document.addEventListener("DOMContentLoaded",n)),s.$=e,s.$$=i,"undefined"!=typeof self&&(self.Awesomplete=s),"object"==typeof exports&&(module.exports=s),s}();

// Debounce - Jason Garber - MIT license
!function(e,t){"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?module.exports=t():e.debounce=t()}(this,function(){"use strict";return function(e,t){var n;return function(){var o=this,i=arguments;clearTimeout(n),n=setTimeout(function(){e.apply(o,i)},t)}}});

	//Omnibar:
var idxOmnibar = function(jsonData){
	//prevent script from running twice or erroring if no omnibar
	if(!document.querySelector('.idx-omnibar-input') || document.querySelector('.awesomplete ul')) {
		return;
	}
	/*
	* Autocomplete
	*/
	var cczList = [];
	var basicPtID = mlsPtIDs[0].mlsPtID;

	// Don't want to create more globals, but the current setup necessitates this without a bigger rewrite.
	var currentAutocompleteData = [];

	//helper function for finding Object properties number
	Object.size = function(obj) {
	    var size = 0, key;
	    for (key in obj) {
	        if (obj.hasOwnProperty(key)) size++;
	    }
	    return size;
	};
	//helper function runs function for each item in DOM array
	var forEach = function (array, callback, scope) {
	  for (var i = 0; i < array.length; i++) {
	    callback.call(scope, i, array[i]);
	  }
	};
	//check field shortname against longname in customFieldsKey object
	var checkFieldName = function(fieldName){
		var displayName = '';
		for(var i = 0; i < Object.size(customFieldsKey); i++){
			var systemName = Object.keys(customFieldsKey)[i];
			var longName = customFieldsKey[systemName];
			if(fieldName === systemName){
				displayName = longName;
			}
		}
		return displayName;
	};
	//helper function for grabbing the name of each item in JSON creating new array
	var createArrays = function(array, newArray, type, fieldName){
		//if zip, do not append state abbreviations
		if(type === 'zip'){
			array.forEach(function(item){
				//filter out blank and Other CCZ from autocomplete dropdown
				if(item.name !== '' && item.name !== 'Other' && item.name !== 'Other State'){
					newArray.push(item.name);
				}
			});
			//if county, append County and State
		} else if(type ==='county') {
			array.forEach(function(item){
				if(item.name !== '' && item.name !== 'Other' && item.name !== 'Other State'){
					newArray.push(item.name + ' County, ' + item.stateAbrv);
				}
			});
			//if city, append State
		} else if(type === 'city'){
			array.forEach(function(item){
				if(item.name !== '' && item.name !== 'Other' && item.name !== 'Other State'){
					newArray.push(item.name + ', ' + item.stateAbrv);
				}
			});
		} else if (type === 'address'){
			array.forEach(function(item){
				if(item !== '' && item !== 'Other' && item !== 'Other State'){
					newArray.push(item);
				}
			});
		} else {
			array.forEach(function(item){
				if(item !== '' && item !== 'Other'){
					newArray.push(item + ' ' + checkFieldName(fieldName));
				}
			});
		}
		return newArray;
	};

	var addAdvancedFields = function(newArray){
		for(var i = 1; i < jsonData.length; i++){
			var idxID = Object.keys(jsonData[i])[0];
			var fieldNumber = Object.size(jsonData[i][idxID]);
			for(var j = 0; j < fieldNumber; j++){
				var fieldName = Object.keys(jsonData[i][idxID][j])[0];
				var fieldValues = jsonData[i][idxID][j][fieldName];
				createArrays(fieldValues, newArray, 'custom', fieldName);
			}
		}
		return cczList;
	}


	//dependent upon createArrays. Creates cczList array
	var buildLocationList = function (data){
		// Hacky fix for addresses not being a core field by default
		if (data[0].core.addresses === undefined) {
			data[0].core.addresses = [];
		}
		return addAdvancedFields(createArrays(data[0].core.addresses, createArrays(data[0].core.zipcodes, createArrays(data[0].core.counties, createArrays(data[0].core.cities, cczList, 'city'), 'county'), 'zip'), 'address'));
	};
	//remove duplicate entries
	var removeDuplicates = function(data) {
		var seen = {};
		var out = [];
		var len = data.length;
		var j = 0;
		for(var i = 0; i < len; i++) {
				var item = data[i];
				if(seen[item] !== 1) {
							seen[item] = 1;
							out[j++] = item;
				}
		}
		return out;
	};

	// The debounce function will wait until a specified amount of time has passed between events before triggering (only once per batch)
	// This function doesn't guarantee the correct order of ajax responses, but one generally get them in order given our debounce value.
	// Can add a timer in this closure to keep track if needed.
	function debounceAjax() {
		var prevDataLength = 0;
		return debounce(function(a, value) {
			if(value.value.length < 1) {
				a.list = a._list.slice(0, a._list.length - prevDataLength);
				prevDataLength = 0;
				return;
			}
			var nonceParam = '_wpnonce=';
			if( ~idxAutocompleteServerObj.url.indexOf('?') ) {
				nonceParam = '&' + nonceParam;
			} else {
				nonceParam = '?' + nonceParam;
			}
			jQuery.ajax({
				url: idxAutocompleteServerObj.url + value.value + nonceParam + idxAutocompleteServerObj.nonce,
			}).done(function(data) {
				// If our data isn't an array of length > 0 then return (this should preserve old behavior)
				if(!Array.isArray(data) || data.length === 0) {
					return;
				}

				currentAutocompleteData = data;

				data = data.map(function(x){return x.value});
				// a.list is a setter with a._list being the actual data array
				// We want to remove the last address entries and add the news ones
				a.list = a._list.slice(0, a._list.length - prevDataLength).concat(data);

				prevDataLength = data.length;
			});
		}, 250);
	}

	// Make the closure
	var triggerAutocomplete = debounceAjax();

	// Initialize Autocomplete of CCZs for each omnibar allowing multiple per page
	forEach(document.querySelectorAll('.idx-omnibar-input'), function (index, value) {
		var a = new Awesomplete(value,{autoFirst: true});
		a.list = removeDuplicates(buildLocationList(jsonData));

		// Add event listener for address autocomplete
		value.addEventListener('input', function() {triggerAutocomplete(a, value)});
	});

	/*
	* Running the Search
	*/
	var foundResult = false;

	var goToResultsPage = function (input, url, additionalQuery, listingID){
		if(typeof agentHeaderID !== 'undefined') {
			additionalQuery = additionalQuery + '&agentHeaderID=' + agentHeaderID;
		}
		if(listingID !== undefined){
			return window.location = url + additionalQuery;
		}
		return window.location = url + additionalQuery + setExtraFieldValues(input);
	};


	var checkExtraFieldValues = function(input, fieldType, resultsQuery){
		var field = input.parentNode.parentNode.querySelectorAll('.idx-omnibar-'+fieldType)[0];
		//if field does not exist, return blank string
		if(typeof field === 'undefined'){
			return '';
		} 
		//if field is entered, use it
		if(field.value){
			return resultsQuery + input.parentNode.parentNode.querySelector('.idx-omnibar-'+fieldType).value;
		} else {
			return '';
		}
	};

	var setExtraFieldValues = function(input){
		var extraValues = '';
		extraValues += checkExtraFieldValues(input, 'min-price', '&lp=');
		extraValues += checkExtraFieldValues(input, 'price', '&hp=');
		extraValues += checkExtraFieldValues(input, 'bed', '&bd=');
		extraValues += checkExtraFieldValues(input, 'bath', '&tb=');
		return extraValues;
	};

	var whatState = function(inputState, idxState, stateException){
		//hardcoded states to allow for user to enter "Glendale", "Glendale, Oregon", or "Glendale, OR"
		var availableStates = [{
			    "name": "Alabama",
			        "abbreviation": "AL"
			}, {
			    "name": "Alaska",
			        "abbreviation": "AK"
			}, {
				"name": "Alberta",
					"abbreviation": "AB"
			}, {
			    "name": "American Samoa",
			        "abbreviation": "AS"
			}, {
			    "name": "Arizona",
			        "abbreviation": "AZ"
			}, {
			    "name": "Arkansas",
			        "abbreviation": "AR"
			}, {
				"name": "Baha California",
					"abbreviation": "BC"
			}, {
				"name": "Bahamas",
					"abbreviation": "BS"
			}, {
			    "name": "British Columbia",
			        "abbreviation": "BC"
			}, {
			    "name": "California",
			        "abbreviation": "CA"
			}, {
			    "name": "Colorado",
			        "abbreviation": "CO"
			}, {
			    "name": "Connecticut",
			        "abbreviation": "CT"
			}, {
			    "name": "Delaware",
			        "abbreviation": "DE"
			}, {
			    "name": "District Of Columbia",
			        "abbreviation": "DC"
			}, {
			    "name": "Federated States Of Micronesia",
			        "abbreviation": "FM"
			}, {
			    "name": "Florida",
			        "abbreviation": "FL"
			}, {
			    "name": "Georgia",
			        "abbreviation": "GA"
			}, {
			    "name": "Guam",
			        "abbreviation": "GU"
			}, {
			    "name": "Hawaii",
			        "abbreviation": "HI"
			}, {
			    "name": "Idaho",
			        "abbreviation": "ID"
			}, {
			    "name": "Illinois",
			        "abbreviation": "IL"
			}, {
			    "name": "Indiana",
			        "abbreviation": "IN"
			}, {
			    "name": "Iowa",
			        "abbreviation": "IA"
			}, {
				"name": "Jamaica",
					"abbreviation": "JM"
			}, {
			    "name": "Kansas",
			        "abbreviation": "KS"
			}, {
			    "name": "Kentucky",
			        "abbreviation": "KY"
			}, {
			    "name": "Louisiana",
			        "abbreviation": "LA"
			}, {
			    "name": "Maine",
			        "abbreviation": "ME"
			}, {
			    "name": "Manitoba",
			        "abbreviation": "MB"
			}, {
			    "name": "Marshall Islands",
			        "abbreviation": "MH"
			}, {
			    "name": "Maryland",
			        "abbreviation": "MD"
			}, {
			    "name": "Massachusetts",
			        "abbreviation": "MA"
			}, {
				"name": "Mexico",
					"abbreviation": "MX"
			}, {
			    "name": "Michigan",
			        "abbreviation": "MI"
			}, {
			    "name": "Minnesota",
			        "abbreviation": "MN"
			}, {
			    "name": "Mississippi",
			        "abbreviation": "MS"
			}, {
			    "name": "Missouri",
			        "abbreviation": "MO"
			}, {
			    "name": "Montana",
			        "abbreviation": "MT"
			}, {
			    "name": "Nebraska",
			        "abbreviation": "NE"
			}, {
			    "name": "Nevada",
			        "abbreviation": "NV"
			}, {
			    "name": "New Brunswick",
			        "abbreviation": "NB"
			}, {
			    "name": "New Hampshire",
			        "abbreviation": "NH"
			}, {
			    "name": "New Jersey",
			        "abbreviation": "NJ"
			}, {
			    "name": "New Mexico",
			        "abbreviation": "NM"
			}, {
			    "name": "New York",
			        "abbreviation": "NY"
			}, {
			    "name": "Newfoundland and Labrador",
			        "abbreviation": "NL"
			}, {
			    "name": "North Carolina",
			        "abbreviation": "NC"
			}, {
			    "name": "North Dakota",
			        "abbreviation": "ND"
			}, {
			    "name": "Northern Mariana Islands",
			        "abbreviation": "MP"
			}, {
			    "name": "Nova Scotia",
			        "abbreviation": "NS"
			}, {
			    "name": "Northwest Territories",
			        "abbreviation": "NT"
			}, {
			    "name": "Nunavut",
			        "abbreviation": "NU"
			}, {
			    "name": "Ohio",
			        "abbreviation": "OH"
			}, {
			    "name": "Oklahoma",
			        "abbreviation": "OK"
			}, {
			    "name": "Ontario",
			        "abbreviation": "ON"
			}, {
			    "name": "Oregon",
			        "abbreviation": "OR"
			}, {
			    "name": "Palau",
			        "abbreviation": "PW"
			}, {
			    "name": "Pennsylvania",
			        "abbreviation": "PA"
			}, {
			    "name": "Prince Edward Island",
			        "abbreviation": "PE"
			}, {
			    "name": "Puerto Rico",
			        "abbreviation": "PR"
			}, {
			    "name": "Quebec",
			        "abbreviation": "QC"
			}, {
			    "name": "Rhode Island",
			        "abbreviation": "RI"
			}, {
			    "name": "Saskatchewan",
			        "abbreviation": "SK"
			}, {
			    "name": "South Carolina",
			        "abbreviation": "SC"
			}, {
			    "name": "South Dakota",
			        "abbreviation": "SD"
			}, {
			    "name": "Tennessee",
			        "abbreviation": "TN"
			}, {
			    "name": "Texas",
			        "abbreviation": "TX"
			}, {
			    "name": "Utah",
			        "abbreviation": "UT"
			}, {
			    "name": "Vermont",
			        "abbreviation": "VT"
			}, {
			    "name": "Virgin Islands",
			        "abbreviation": "VI"
			}, {
			    "name": "Virginia",
			        "abbreviation": "VA"
			}, {
			    "name": "Washington",
			        "abbreviation": "WA"
			}, {
			    "name": "West Virginia",
			        "abbreviation": "WV"
			}, {
			    "name": "Wisconsin",
			        "abbreviation": "WI"
			}, {
			    "name": "Wyoming",
			        "abbreviation": "WY"
			}, {
			    "name": "Yukon",
			        "abbreviation": "YT"
			}]
			//if no state is entered, return true
		if(inputState === undefined || stateException){
			return true;
		}
		//for each state, see if the entered state name or abbreviation matches this list. This allows for "Glendale, OR" and "Glendale, CA" to use the correct city
		for(var i = 0; i < availableStates.length; i++){
			if(availableStates[i].abbreviation === inputState.toUpperCase() || availableStates[i].name.toUpperCase() === inputState.toUpperCase()){
				if(availableStates[i].abbreviation === idxState){
					return true;
				}
			}
		}

	}
	var isCounty = function(inputCounty, listType){
		if(inputCounty === undefined){
			return true;
		} else {
			if(listType === 'counties'){
				return true;
			} else {
				return false;
			}
		}
	}

	//checks against the cities, counties, and zipcodes. If no match, runs callback
	var checkAgainstList = function (input, list, listType, callback){
		var inputFiltered = input.value.toLowerCase().split(', ')[0];
		var stateException = false;
		//prevent edge case of Jersey City cities from breaking functionality
		if(inputFiltered === 'jc'){
			inputFiltered = 'jc, ' + input.value.toLowerCase().split(', ')[1];
			var stateException = true;
		}
		for(var i=0; i < list.length; i++){
			//filter out blank and county from input and check for appended state
			if (inputFiltered.split(' county')[0] === list[i].name.toLowerCase() && whatState(input.value.split(', ')[1], list[i].stateAbrv, stateException) && isCounty(inputFiltered.split(' county')[1], listType) && input.value) {
				switch(listType){
					case 'cities':
						foundResult = true;
						goToResultsPage(input, idxUrl, '?pt=' + basicPtID + '&ccz=city&city[]=' + list[i].id + '&srt=' + sortOrder);
						break;
					case 'counties':
						foundResult = true;
						goToResultsPage(input, idxUrl, '?pt=' + basicPtID + '&ccz=county&county[]=' + list[i].id + '&srt=' + sortOrder);
						break;
					case 'zipcodes':
						foundResult = true;
						goToResultsPage(input, idxUrl, '?pt=' + basicPtID + '&ccz=zipcode&zipcode[]=' + list[i].id + '&srt=' + sortOrder);
						break;
				}
			} else if (foundResult === false && i == list.length - 1) {
				return callback;
			}
		}
	};
	//check input against advanced fields
	var advancedList = function(input){
		for(var i = 1; i < jsonData.length; i++){
			var idxID = Object.keys(jsonData[i])[0];
			var fieldNumber = Object.size(jsonData[i][idxID]);
			//check for mlsPtID (skip 0 index as it is for basic searches)
			for(var j = 1; j < mlsPtIDs.length; j++){
				keyIdxID = mlsPtIDs[j].idxID;
				keyMlsPtID = mlsPtIDs[j].mlsPtID;
				//if mlsPtIDs global object property matches idxID, use that property type
				if(keyIdxID === idxID){
					var mlsPtID = keyMlsPtID;
				}
			}
			//if no default pt, default to 1
			if(typeof mlsPtID === 'undefined'){
				var mlsPtID = 1;
			}

			for(var j = 0; j < fieldNumber; j++){
				var fieldName = Object.keys(jsonData[i][idxID][j])[0];
				var fieldValues = jsonData[i][idxID][j][fieldName];

				forEach(fieldValues, function(index, value){
					if(input.value !== '' && (input.value.toLowerCase() === value.toLowerCase() || input.value.toLowerCase() === (value + ' ' + checkFieldName(fieldName)).toLowerCase())){
						foundResult = true;
						return goToResultsPage(input, idxUrl, '?pt=' + mlsPtID + '&idxID=' + idxID + '&aw_' + fieldName + '=' + value + '&srt=' + sortOrder);
					}
				})
				if(foundResult){
					return;
				}

			}

		}
		if(foundResult === false){
			return notOnList(input);
		}
	}

	//callback for checkAgainstList function. Inherits global idxUrl variable from widget HTML script
	var notOnList = function (input) {
		var mlsPtId = basicPtID;
		var AddressFieldType = '';
		var AddressMLS = '';

		// The address autocomplete will never be on the list, so let's check for the mls property type here.
		currentAutocompleteData.forEach(function(x){
			if(x.value == input.value) {
				mlsPtIDs.forEach(function(y) {
					if(y.idxID == x.mls) {
						mlsPtId = y.mlsPtID;
					}
				});
				AddressFieldType = x.field;
				AddressMLS = x.mls;
			}
		});

		// Quick fix for if using the address autocomplete
		if(AddressFieldType === 'address') {
			goToResultsPage(input, idxUrl, '?idxID=' + AddressMLS + '&pt=' + mlsPtId + '&aw_address=' + input.value);
			return;
		}
		
		var hasSpaces = /\s/g.test(input.value);
		if (!input.value) {
			//nothing in input
			goToResultsPage(input, idxUrl, '?pt=' + mlsPtId + '&srt=' + sortOrder);
		} else if(hasSpaces === false && parseInt(input.value) !== isNaN) {
			//MLS Number/ListingID
			var listingID = true;
			var agentHeaderID = false;
			goToResultsPage(input, idxUrl, '?csv_listingID=' + input.value, listingID);
		} else {
			//address (split into number and street)
			var addressSplit = input.value.split(' ');
			//if first entry is number, search for street number otherwise search for street name
			if(Number(addressSplit[0]) > 0){
				var addressName = addressSplit[1];
				// don't assume street name is one word, combine address pieces
				if(addressSplit.length > 1) {
					for(var i=2, len=addressSplit.length; i < len; i++){
						addressName += '+' + addressSplit[i];
					}
				}
				goToResultsPage(input, idxUrl, '?pt=' + mlsPtId + '&a_streetNumber=' + addressSplit[0] + '&aw_address=' + addressName + '&srt=' + sortOrder);
			} else if(input.value === idxOmnibarPlaceholder){
				//prevent placeholder from interfering with results URL
				goToResultsPage(input, idxUrl, '?pt=' + mlsPtId + '&srt=' + sortOrder);
			} else {
				//search by just street name (without state or city if comma is used)
				goToResultsPage(input, idxUrl, '?pt=' + mlsPtId + '&aw_address=' + input.value.split(', ')[0] + '&srt=' + sortOrder);
			}
		}
	};


	var runSearch = function(event) {
		event.preventDefault();
		var input = event.target.querySelector('.idx-omnibar-input');
		checkAgainstList(input, jsonData[0].core.cities, 'cities', checkAgainstList(input, jsonData[0].core.counties, 'counties', checkAgainstList(input, jsonData[0].core.zipcodes, 'zipcodes')));
		if(foundResult === false){
			advancedList(input);
		}
	};

	//on submit, run the search (applies this to each omnibar)
	forEach(document.querySelectorAll('.idx-omnibar-form'), function(index, value){value.addEventListener('submit', runSearch);});

	//make omnibar fit sidebars better by pushing button to bottom for basic omnibr
	forEach(document.querySelectorAll('.idx-omnibar-original-form'), function(index, value){
		if(value.offsetWidth < 400){
			value.classList.add('idx-omnibar-mini');
		}
	})
};