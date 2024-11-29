const { createApp, ref, reactive, watch, computed } = Vue;

createApp({
  setup() {
    console.log("Vue is mounted!!!");

    // Reactive data for the search query and search results
    const searchQuery = ref("");
    const searchResults = ref([]);    
    const searchResultsLP = ref([]);
    const destinations = reactive([]); // Store all loaded destinations
    const isListVisible = ref(false); // Define the visibility of the results list    
    // const adultsCount = ref(2);
    const checkinDate = ref("");
    const nightsCount = ref(10);
    const isLoading = ref(false);
    const searchStatus = ref("Search Results");
    const showFilters = ref(false);
    const filteredResultsLP = ref([]);
    const selectedRatings = ref([]);
    const selectedTypes = ref([]);
    const roomsCount = ref(1); 
    const rooms = ref([
      { adults: 2, children: 0 } 
    ]);
    
    // Function to handle the destination search
    const searchDestinations = () => {
      if (searchQuery.value.length >= 2) {
        console.log("Search query:", searchQuery.value);       
    
        const filteredResults = destinations.filter((destination) => {
          if (typeof destination === "string") {
            const searchString = destination.toLowerCase();
            return searchString.includes(searchQuery.value.toLowerCase());
          }
          return false;
        });
    
        console.log("Filtered results:", filteredResults);
    
        if (Array.isArray(searchResults)) {
          searchResults.splice(0, searchResults.length, ...filteredResults);
        } else {
          searchResults.value = filteredResults;
        }
    
        isListVisible.value = filteredResults.length > 0;
      } else {
        if (Array.isArray(searchResults)) {
          searchResults.splice(0, searchResults.length);
        } else {
          searchResults.value = [];
        }
        isListVisible.value = false;
      }
    };
    
    // Function to load destinations from the server when the page loads
    const loadDestinations = async () => {
      try {
        const response = await fetch(brosTravelData.ajaxurl, {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            action: "get_bros_travel_all_destinations",
            nonce: brosTravelData.nonce,
          }),
        });
    
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
    
        const text = await response.text();
    
        if (text.trim() === "") {
          throw new Error("Empty response body");
        }
    
        const data = JSON.parse(text);
        console.log("Parsed data:", data);
    
        if (data.success && data.data) {
          const locationsArray = Object.values(data.data);
    
          // Directly modify the reactive array
          destinations.splice(0, destinations.length, ...locationsArray);
    
          
        } else {
          console.error("Error: locations not found or not valid.");
        }
      } catch (error) {
        console.error("Error while loading destinations:", error);
      }
    };

    const loadAvailableProperties = async () => {
      isLoading.value = true;
      searchStatus.value = "Searching data...";
      try {
        // Prepare rooms data as JSON
        const roomsData = rooms.value.map((room) => ({
          adults: room.adults,
          children: Array.isArray(room.children) ? room.children : [room.children],
        }));
    
       
        // Prepare body with all parameters
        const bodyParams = new URLSearchParams({
          action: "get_bros_travel_sa_properties",
          nonce: brosTravelData.nonce,
          region: "Halkidiki", // Destination input
          checkinDate: formattedCheckinDate.value, // Check-in date input
          nights: nightsCount.value || "0", // Nights dropdown
          rooms: JSON.stringify(roomsData), // Rooms data as JSON
        });
    
        // Send request to server
        const response = await fetch(brosTravelData.ajaxurl, {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: bodyParams,
        });
    
        const data = await response.json();
        console.log("Available Properties: ", data);
    
        if (data.success) {
          // Map response data to the required format for Vue component
          searchResultsLP.value = data.data.result.map((property) => ({
            property_id: property.propertyid,
            property_name: property.name,
            property_image: property.image,
            property_rating: property.rating,
            location_name: property.location_data.name,
            region: property.location_data.region,
            subregion: property.location_data.subregion,
            country: property.location_data.country,
            property_description: property.description,
            rooms: property.availableRooms.map((availableRoom) =>
              availableRoom.rooms.map((room) => ({
                type: room.type,
                available: room.available,
                max: room.max,
                board: room.board,
                price: room.price.toFixed(2),
              }))
            ).flat(),           
          }));
    
          // Update status based on results
          if (searchResultsLP.value.length === 0) {
            searchStatus.value = "No properties found";
          } else if (searchResultsLP.value.length === 1) {
            searchStatus.value = "1 property found";
          } else {
            searchStatus.value = `${searchResultsLP.value.length} properties found`;
          }
    
          // Update stars for all properties (if applicable)
          updateStarsInResults();
        } else {
          searchResultsLP.value = [];
          searchStatus.value = "No properties found";
        }
      } catch (error) {
        console.error("Error during search:", error);
        searchResultsLP.value = [];
        searchStatus.value = "Error during search";
      } finally {
        isLoading.value = false;
      }
    };

    const formattedCheckinDate = computed(() => {
      if (!checkinDate.value) return ""; 
      const date = new Date(checkinDate.value);
      if (!isNaN(date.getTime())) {
        return date.toISOString().split("T")[0]; 
      }
      return ""; 
    });   
    

    // Function to generate star images based on the property rating
    const generateStars = (rating) => {
        const starSrc = "/wp-content/plugins/bros-travel-plugin/assets/images/full-star.svg"; // Path to star image
        let starsHtml = "";
  
        for (let i = 0; i < rating; i++) {
          starsHtml += `<img src="${starSrc}" alt="Star" class="star-icon" />`; // Add a star for each rating point
        }
  
        return starsHtml; // Return the generated HTML for stars
    };
  
      // Function to update stars in the DOM when search results are loaded
    const updateStarsInResults = () => {
        searchResultsLP.value.forEach((result) => {
          if (result.property_rating) {             
            const ratingContainer = document.querySelector(
              `.property-rating[data-id="${result.property_id}"] .stars`
            );
      
            if (ratingContainer) {
              ratingContainer.innerHTML = generateStars(result.property_rating); 
            }
          }
        });
    };
      

    // Watch for changes in search query and filter results accordingly
    watch(searchQuery, searchDestinations, loadAvailableProperties);

    // Load destinations when the component is mounted
    loadDestinations();

    // Methods for handling focus, blur, and click outside
    const showList = () => {
      isListVisible.value = true;
    };

    const hideList = () => {
      // Hide the list after a slight delay to allow for clicks
      setTimeout(() => {
        isListVisible.value = false;
      }, 200);
    };

    const selectDestination = (result) => {
      searchQuery.value = result; // Set the selected destination in the input
      console.log("Selected destination:", searchQuery.value);
      isListVisible.value = false; // Close the list
    };
  

    const getTrimmedDestination = (input) => {
        return input.split(' / ')[0];
    };

    const updateRooms = () => {
      const newRooms = [];
      for (let i = 0; i < roomsCount.value; i++) {
        newRooms.push({
          adults: rooms.value[i]?.adults || 2, // Keep existing value or default to 1
          children: rooms.value[i]?.children !== undefined ? rooms.value[i].children : 0 // Keep existing value or default to 1
        });
      }
      rooms.value = newRooms; // Update the reactive array
    };

    /*FILTERS*/

    const toggleFilters = () => {
        showFilters.value = !showFilters.value;
        
        if (showFilters.value) {
          document.querySelector('.filters-wrapper').classList.add('visible');
        } else {
          document.querySelector('.filters-wrapper').classList.remove('visible');
        }
    };

    const filterByRating = () => {
        if (selectedRatings.value.length === 0) {
            filteredResultsLP.value = [...searchResultsLP.value];
        } else {
            filteredResultsLP.value = searchResultsLP.value.filter((result) =>
                selectedRatings.value.includes(result.property_rating)
            );
        }
    
        if (filteredResultsLP.value.length === 0) {
            searchStatus.value = "No properties found";
        } else if (filteredResultsLP.value.length === 1) {
            searchStatus.value = "1 property found";
        } else {
            searchStatus.value = `${filteredResultsLP.value.length} properties found`;
        }
    
        console.log("Filtered Results by rating:", filteredResultsLP.value);
    };
    
      
      
      

    return {
      searchQuery,
      searchResults,
      searchResultsLP,
      searchDestinations,
      destinations,
      isListVisible, // Make isListVisible available in the template
      showList,
      hideList,
      selectDestination,
      // adultsCount,
      nightsCount,      
      searchStatus,
      generateStars,
      showFilters,
      toggleFilters,
      filterByRating,
      filteredResultsLP,
      selectedRatings,
      loadAvailableProperties,
      checkinDate,
      formattedCheckinDate,
      roomsCount,
      rooms,
      updateRooms
    };
  },
}).mount("#brosSearchApp");
