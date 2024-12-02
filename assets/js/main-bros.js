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
    const selectedAvailable = ref([]); 
    const selectedBoard = ref([]);   
    const roomsCount = ref(1); 
    const rooms = ref([
      { adults: 2, children: 0 } 
    ]);
    const accommodationTypes = ref([
      { label: "Hotel", value: "hotel" },
      { label: "Luxury villa", value: "villa" },
      { label: "Private accommodation", value: "private" },
    ]);
    const availableTypes = ref([
      { label: "Available", value: "available" },
      { label: "On request", value: "on_request" },
      { label: "Stop sale", value: "stop_sale" },
    ]);
    const boardTypes = ref([
      { label: "AI", value: "AI" },
      { label: "BB", value: "BB" },
      { label: "FB", value: "FB" },
      { label: "HB", value: "HB" },
      { label: "RR", value: "RR" },
      { label: "UAI", value: "UAI" }      
    ]);
   
    // Define reactive variables for selected filters
    const selectedRegion = ref(null);
    const selectedSubregion = ref(null);
    const selectedLocation = ref(null);
    const selectedProperty = ref(null);

    
    // Function to handle the destination search
    const searchDestinations = () => {
      if (searchQuery.value.length >= 2) {          
    
        const filteredResults = destinations.filter((destination) => {
          if (typeof destination === "string") {
            const searchString = destination.toLowerCase();
            return searchString.includes(searchQuery.value.toLowerCase());
          }
          return false;
        });
    
        // console.log("Filtered results:", filteredResults);
    
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
      searchResultsLP.value = []; // Clear previous search results
      filteredResultsLP.value = []; // Clear previous filtered results
      isLoading.value = true;
      searchStatus.value = "Searching data...";
      try {
              const extractPart = (input, partIndexFromEnd) => {
              const parts = input.split(" / ").map((part) => part.trim()); 
              return parts.length >= Math.abs(partIndexFromEnd) ? parts[parts.length + partIndexFromEnd] : null;
          };
  
        
          selectedRegion.value = extractPart(searchQuery.value, -2); 
          selectedSubregion.value = extractPart(searchQuery.value, -3); 
          selectedLocation.value = extractPart(searchQuery.value, -4); 
          selectedProperty.value = extractPart(searchQuery.value, -5);  

          const roomsData = rooms.value.map((room) => ({
              adults: room.adults,
              children: Array.isArray(room.children) ? room.children : [room.children],
          }));         
        
  
          const bodyParams = new URLSearchParams({
              action: "get_bros_travel_sa_properties",
              nonce: brosTravelData.nonce,
              region: selectedRegion.value || "", 
              checkinDate: formattedCheckinDate.value,
              nights: nightsCount.value,
              rooms: JSON.stringify(roomsData),
          });
  
          const response = await fetch(brosTravelData.ajaxurl, {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body: bodyParams,
          });
  
          const data = await response.json();
          console.log("Available Properties: ", data);
  
          if (data.success) {              
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
                  type: property.type,
                  rooms: property.availableRooms[0]?.rooms.map((room) => ({
                    type: room.type,
                    available: room.available,
                    max: room.max,
                    board: room.board,
                    price: room.price.toFixed(2),
                  })) || [],
              }));
  
              
              filteredResultsLP.value = searchResultsLP.value.filter((result) => {
                  const matchesRegion = selectedRegion.value ? result.region === selectedRegion.value : true;
                  const matchesSubregion = selectedSubregion.value ? result.subregion === selectedSubregion.value : true;
                  const matchesLocation = selectedLocation.value ? result.location_name === selectedLocation.value : true;
                  const matchesProperty = selectedProperty.value ? result.property_name === selectedProperty.value : true;
  
                  return matchesRegion && matchesSubregion && matchesLocation && matchesProperty;
              });
  
              if (filteredResultsLP.value.length === 0) {
                  searchStatus.value = "No properties found";
              } else if (filteredResultsLP.value.length === 1) {
                  searchStatus.value = "1 property found";
              } else {
                  searchStatus.value = `${filteredResultsLP.value.length} properties found`;
              }
          } else {
              searchResultsLP.value = [];
              filteredResultsLP.value = [];
              searchStatus.value = "No properties found";
          }
      } catch (error) {
          console.error("Error during search:", error);
          searchResultsLP.value = [];
          filteredResultsLP.value = [];
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


    const extractRegion = (input) => {
      if (!input) return ""; 
    
      const parts = input.split(" / ").map(part => part.trim()); 
      if (parts.length < 2) return ""; 
    
      return parts[parts.length - 2];
    };
    

    // Function to generate star images based on the property rating
    const generateStars = (rating) => {
        const starSrc = "/wp-content/plugins/bros-travel-plugin/assets/images/full-star.svg"; // Path to star image
        let starsHtml = "";
  
        for (let i = 0; i < rating; i++) {
          starsHtml += `<img src="${starSrc}" alt="Star" class="star-icon" />`; // Add a star for each rating point
        }
  
        return starsHtml; // Return the generated HTML for stars
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
         
    const applyFilters = () => {
      console.log("Applying Filters...");
      console.log("Selected Ratings:", selectedRatings.value);
      console.log("Selected Types:", selectedTypes.value);
      console.log("Selected Availability:", selectedAvailable.value);
      console.log("Selected Board:", selectedBoard.value);
      // Reset filtered results if no filters are selected
      if (
          selectedRatings.value.length === 0 &&
          selectedTypes.value.length === 0 &&
          selectedAvailable.value.length === 0 &&
          selectedBoard.value.length === 0
      ) {
          console.log("No filters selected. Resetting to search context.");
          filteredResultsLP.value = searchResultsLP.value.filter((result) => {
              const matchesRegion = selectedRegion.value ? result.region === selectedRegion.value : true;
              const matchesSubregion = selectedSubregion.value ? result.subregion === selectedSubregion.value : true;
              const matchesLocation = selectedLocation.value ? result.location_name === selectedLocation.value : true;
              const matchesProperty = selectedProperty.value ? result.property_name === selectedProperty.value : true;
  
              return matchesRegion && matchesSubregion && matchesLocation && matchesProperty;
          });
          updateSearchStatus();
          return;
      }
  
      // Apply filtering logic
      filteredResultsLP.value = searchResultsLP.value.filter((result) => {
          const matchesRating = selectedRatings.value.length > 0
              ? selectedRatings.value.includes(result.property_rating)
              : true;
  
          const matchesType = selectedTypes.value.length > 0
              ? selectedTypes.value.includes(result.type?.toLowerCase())
              : true;
  
          const matchesAvailability = selectedAvailable.value.length > 0
              ? result.rooms.some((room) =>
                    selectedAvailable.value.includes(room.available)
                )
              : true;

          const matchesBoard = selectedBoard.value.length > 0
          ? result.rooms.some((room) =>
                selectedBoard.value.includes(room.board)
            )
          : true;
  
          const matchesRegion = selectedRegion.value ? result.region === selectedRegion.value : true;
          const matchesSubregion = selectedSubregion.value ? result.subregion === selectedSubregion.value : true;
          const matchesLocation = selectedLocation.value ? result.location_name === selectedLocation.value : true;
          const matchesProperty = selectedProperty.value ? result.property_name === selectedProperty.value : true;
  
          return matchesRating && matchesType && matchesAvailability && matchesBoard && matchesRegion && matchesSubregion && matchesLocation && matchesProperty;
      });
  
      updateSearchStatus();
  };
  
  
        
         
    const updateSearchStatus = () => {
        if (filteredResultsLP.value.length === 0) {
            searchStatus.value = "No properties found";
        } else if (filteredResultsLP.value.length === 1) {
            searchStatus.value = "1 property found";
        } else {
            searchStatus.value = `${filteredResultsLP.value.length} properties found`;
        }
    };
      
        
          


    return {
      searchQuery,
      searchResults,
      searchResultsLP,
      searchDestinations,
      destinations,
      isListVisible, 
      showList,
      hideList,
      selectDestination,      
      nightsCount,      
      searchStatus,
      generateStars,
      showFilters,
      toggleFilters,      
      applyFilters,
      filteredResultsLP,      
      loadAvailableProperties,
      checkinDate,
      formattedCheckinDate,
      roomsCount,
      rooms,
      updateRooms,
      selectedRatings,
      selectedTypes,
      selectedAvailable,
      selectedBoard,
      accommodationTypes,
      availableTypes,
      boardTypes
    };
  },
}).mount("#brosSearchApp");
