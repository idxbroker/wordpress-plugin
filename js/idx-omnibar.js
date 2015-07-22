		var idxOmnibar = function(jsonData){
			//prevent script from running twice or erroring if no omnibar
		if(document.querySelector('.idx-omnibar-input') && !document.querySelector('.awesomplete')){
			/*
			* Autocomplete
			*/

			var cczList = [];

			//helper function runs function for each item in DOM array
			var forEach = function (array, callback, scope) {
			  for (var i = 0; i < array.length; i++) {
			    callback.call(scope, i, array[i]);
			  }
			};

			//helper function for grabbing the name of each item in JSON creating new array
			var createArrays = function(array, newArray){
				array.forEach(function(item){newArray.push(item.name);});
				return newArray;
			};

			//dependent upon createArrays. Creates cczList array
			var buildLocationList = function (data){
				return createArrays(data.zipcodes, createArrays(data.counties, createArrays(data.cities, cczList)));
			};

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



			//Initialize Autocomplete of CCZs for each omnibar allowing multiple per page
			forEach(document.querySelectorAll('.idx-omnibar-input'), function (index, value) {
				new Awesomplete(value).list = removeDuplicates(buildLocationList(jsonData));
			});


		/*
		* Running the Search
		*/
		var foundResult = false;

		var goToResultsPage = function (input, url, additionalquery, listingID){
			if(listingID !== undefined){
				return window.location = url + additionalquery;
			}
			return window.location = url + additionalquery + setExtraFieldValues(input);
		};


		var checkExtraFieldValues = function(input, fieldType, resultsQuery){
			if(input.parentNode.parentNode.querySelector('.idx-omnibar-'+fieldType).value){
				return resultsQuery + input.parentNode.parentNode.querySelector('.idx-omnibar-'+fieldType).value;
			} else {
				return '';
			}
		};

		var setExtraFieldValues = function(input){
			var extraValues = '';
			extraValues += checkExtraFieldValues(input, 'price', '&hp=');
			extraValues += checkExtraFieldValues(input, 'bed', '&bd=');
			extraValues += checkExtraFieldValues(input, 'bath', '&tb=');
			return extraValues;
		};

		//checks against the cities, counties, and zipcodes. If no match, runs callback
		var checkAgainstList = function (input, list, listType, callback){
			for(var i=0; i < list.length; i++){
				if (input.value.toLowerCase() == list[i].name.toLowerCase()) {
					switch(listType){
						case 'cities':
							foundResult = true;
							goToResultsPage(input, idxUrl, '?ccz=city&city[]=' + jsonData.cities[i].id);
							break;
						case 'counties':
							foundResult = true;
							goToResultsPage(input, idxUrl, '?ccz=county&county[]=' + jsonData.counties[i].id);
							break;
						case 'zipcodes':
							foundResult = true;
							goToResultsPage(input, idxUrl, '?ccz=zipcode&zipcode[]=' + jsonData.zipcodes[i].id);
							break;
					}
				} else if (foundResult === false && i == list.length - 1) {
					return callback;
				}
			}
		};

		//callback for checkAgainstList function. Inherits global idxUrl variable from widget HTML script
		var notOnList = function (input) {
				var hasSpaces = /\s/g.test(input.value);
				if (!input.value) {
					//nothing in input
					goToResultsPage(input, idxUrl, '?pt=all');
				} else if(hasSpaces === false && parseInt(input.value) !== isNaN) {
					//MLS Number/ListingID
					var listingID = true;
					goToResultsPage(input, idxUrl, '?csv_listingID=' + input.value, listingID);
				} else {
					//address (split into number and street)
					var addressSplit = input.value.split(' ');
					//if first entry is number, search for street number otherwise search for street name
					if(Number(addressSplit[0]) > 0){
						goToResultsPage(input, idxUrl, '?a_streetNumber=' + addressSplit[0] + '&aw_streetName=' + addressSplit[1]);
					} else if(addressSplit[0] === 'City,'){
						//prevent placeholder from interfering with results URL
						goToResultsPage(input, idxUrl, '?pt=all');
					} else {
						goToResultsPage(input, idxUrl, '?aw_streetName=' + addressSplit[0]);
					}
				}
			};

			var runSearch = function(event) {
				event.preventDefault();
				var input = event.target.querySelector('.idx-omnibar-input');
				checkAgainstList(input, jsonData.zipcodes, 'zipcodes', checkAgainstList(input, jsonData.counties, 'counties', checkAgainstList(input, jsonData.cities, 'cities')));
				if(foundResult === false){
					notOnList(input);
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


		}
	};
