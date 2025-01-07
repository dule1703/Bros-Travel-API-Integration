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
    const priceRange = reactive({ min: 0, max: 0 });
    const selectedPrice = reactive({ min: 0, max: 0 });
    const selectedBoard = ref([]);   
    const roomsCount = ref(1); 
    const rooms = ref([
      { adults: 2, children: 0, childAges: [] }
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
   
    const selectedRegion = ref(null);
    const selectedSubregion = ref(null);
    const selectedLocation = ref(null);
    const selectedProperty = ref(null);



    /***FUNCTIONS***/
    // Function to handle the destination search

    const searchDestinations = () => {
      if (searchQuery.value.length >= 2) {
        const filteredResults = destinations.filter((destination) => {
          // Ensure we are checking the `text` property of each destination object
          if (destination.text) {
            const searchString = destination.text.toLowerCase();
            return searchString.includes(searchQuery.value.toLowerCase());
          }
          return false;
        });
    
        // Update the searchResults array reactively
        searchResults.value = filteredResults;
    
        // Show the list if there are matching results
        isListVisible.value = filteredResults.length > 0;
      } else {
        // Reset the search results and hide the list if the query is too short
        searchResults.value = [];
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
    
        if (data.success && data.data) {
          const locationsArray = Object.values(data.data);
    
          // Add icons based on the number of '/' in the string
          const destinationsWithIcons = locationsArray.map((location) => {
            const slashCount = (location.match(/\//g) || []).length;
            const icon =
              slashCount === 4
                ? "/wp-content/plugins/bros-travel-plugin/assets/images/bed.svg"
                : "/wp-content/plugins/bros-travel-plugin/assets/images/place.svg";
            return {
              text: location,
              icon: icon,
            };
          });
    
          // Update the reactive destinations array
          destinations.splice(0, destinations.length, ...destinationsWithIcons);
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
        resetFilters();

        try {
                const extractPart = (input, partIndexFromEnd) => {
        if (!input) return null; // Handle empty or null input
        const parts = input.split(" / ").map((part) => part.trim());
        return parts.length >= Math.abs(partIndexFromEnd)
          ? parts[parts.length + partIndexFromEnd]
          : null;
    };

    // Extract query parts
        selectedRegion.value = extractPart(searchQuery.value, -2);
        selectedSubregion.value = extractPart(searchQuery.value, -3);
        selectedLocation.value = extractPart(searchQuery.value, -4);
        selectedProperty.value = extractPart(searchQuery.value, -5);
 


          const roomsData = rooms.value.map((room) => ({
            adults: room.adults,
            children: room.childAges.filter((age) => age !== null), // Filter out `null` ages
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
                    price:  room.price 
                        ? (parseFloat(room.price) * 1.11).toFixed(2) 
                        : "N/A", 
                    initialPrice: room.initialPrice 
                        ? (parseFloat(room.initialPrice) * 1.11).toFixed(2) 
                        : null,
                    specialOffers: room.specialOffers || []
                  })) || [],
              }));

               // Filter properties by selected region, subregion, or location
            const filteredProperties = searchResultsLP.value.filter((property) => {
              const matchesRegion = selectedRegion.value
          ? property.region === selectedRegion.value
          : true;
        const matchesSubregion = selectedSubregion.value
          ? property.subregion === selectedSubregion.value
          : true;
        const matchesLocation = selectedLocation.value
          ? property.location_name === selectedLocation.value
          : true;
        const matchesProperty = selectedProperty.value
          ? property.property_name === selectedProperty.value
          : true;

              return matchesRegion && matchesSubregion && matchesLocation && matchesProperty;
          });
  
          const allPrices = filteredProperties.flatMap((property) => property.rooms.map((room) => parseFloat(room.price)));
              
              if (allPrices.length > 0) {
                priceRange.min = Math.min(...allPrices);
                priceRange.max = Math.max(...allPrices);
                selectedPrice.min = priceRange.min;
                selectedPrice.max = priceRange.max;
            } else {
                priceRange.min = 0;
                priceRange.max = 0;
                selectedPrice.min = 0;
                selectedPrice.max = 0;
            }
        
          // Set filtered results based on selected criteria
          filteredResultsLP.value = filteredProperties;
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
      isListVisible.value = false; // Close the list
    };
  
    const updateRooms = () => {
      const newRooms = []; // Create a new array to hold the updated room data
      for (let i = 0; i < roomsCount.value; i++) {
          // Get the current room data or initialize default values if it doesn't exist
          const currentRoom = rooms.value[i] || { adults: 2, children: 0, childAges: [] };
  
          // Get the number of children in the current room
          const childrenCount = currentRoom.children || 0;
  
          // Ensure the `childAges` array matches the number of children
          const childAges = currentRoom.childAges || [];
  
          if (childAges.length < childrenCount) {
              // Add additional entries (default to `null`) if the number of child ages is less than the number of children
              for (let j = childAges.length; j < childrenCount; j++) {
                  childAges.push(null); // Default value for each new child
              }
          } else if (childAges.length > childrenCount) {
              // Remove extra entries if the number of child ages exceeds the number of children
              childAges.splice(childrenCount);
          }
  
          // Push the updated room data to the newRooms array
          newRooms.push({
              adults: currentRoom.adults, // Retain the number of adults
              children: childrenCount, // Set the number of children
              childAges // Update the child ages array
          });
      }
      // Replace the old rooms array with the updated one
      rooms.value = newRooms;     
  };
  
    const getOrdinalSuffix = (number) => {
      const j = number % 10;
      const k = number % 100;
      if (j === 1 && k !== 11) return "st";
      if (j === 2 && k !== 12) return "nd";
      if (j === 3 && k !== 13) return "rd";
      return "th";
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
  
      filteredResultsLP.value = searchResultsLP.value
          .map((property) => {
              const filteredRooms = property.rooms.filter((room) => {                 
  
                  const roomPrice = parseFloat(room.price);
                  const matchesPrice =
                      roomPrice >= (selectedPrice.min || 0) &&
                      roomPrice <= (selectedPrice.max || 0);
  
                  const matchesAvailability =
                      selectedAvailable.value.length > 0
                          ? selectedAvailable.value.includes("available")
                              ? typeof room.available === "number" && room.available > 0
                              : selectedAvailable.value.includes(room.available)
                          : true;
  
                  const matchesBoard =
                      selectedBoard.value.length > 0
                          ? selectedBoard.value.includes(room.board)
                          : true;
  
                  return matchesPrice && matchesAvailability && matchesBoard;
              });  
           
  
              if (filteredRooms.length > 0) {
                  return {
                      ...property,
                      rooms: filteredRooms,
                  };
              }
              return null;
          })
          .filter((property) => property !== null)
          .filter((property) => {
              const matchesRating =
                  selectedRatings.value.length > 0
                      ? selectedRatings.value.includes(property.property_rating)
                      : true;
  
              const matchesType =
                  selectedTypes.value.length > 0
                      ? selectedTypes.value.includes(property.type?.toLowerCase())
                      : true;
  
              const matchesRegion = selectedRegion.value
                  ? property.region === selectedRegion.value
                  : true;
  
              const matchesSubregion = selectedSubregion.value
                  ? property.subregion === selectedSubregion.value
                  : true;
  
              const matchesLocation = selectedLocation.value
                  ? property.location_name === selectedLocation.value
                  : true;
  
              const matchesProperty = selectedProperty.value
                  ? property.property_name === selectedProperty.value
                  : true;
  
              return (
                  matchesRating &&
                  matchesType &&
                  matchesRegion &&
                  matchesSubregion &&
                  matchesLocation &&
                  matchesProperty
              );
          });  

      updateSearchStatus();
  };
  
  
  
  
    const resetFilters = () => {     
      selectedRatings.value = []; // Reset selected ratings
      selectedTypes.value = []; // Reset selected accommodation types
      selectedAvailable.value = []; // Reset selected availability
      selectedBoard.value = []; // Reset selected board types
      selectedPrice.min = priceRange.min; // Reset price range to default
      selectedPrice.max = priceRange.max; // Reset price range to default
      filteredResultsLP.value = []; // Clear any previously filtered results
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
             
    const showDatepicker = (event) => {
      event.target.showPicker?.();       
    };    
    
    const getAvailabilityClass = (status) => {
      switch (true) {
        case status === "stop_sale":
          return "availability-badge stop-sale-badge";
        case status === "on_request":
          return "availability-badge on-request-badge";
        case typeof status === "number" && status > 0:
          return "availability-badge available-badge";
        default:
          return "availability-badge"; // Default class
      }
    };
    
    const getAvailabilityLabel = (status) => {
      switch (true) {
        case status === "stop_sale":
          return "Stop Sale";
        case status === "on_request":
          return "On Request";
        case typeof status === "number" && status > 0:
          return "Available";
        default:
          return "Unknown"; // Fallback for unexpected values
      }
    };
    
    const renderRoomDetails = (room) => {
      // Initialize special offers HTML
      let specialOffersHTML = "";
  
      // Render special offers if available
      if (room.specialOffers && Array.isArray(room.specialOffers) && room.specialOffers.length > 0) {
          specialOffersHTML = room.specialOffers
              .map(
                  (offer) =>
                      `<span class="offer-badge">
                          ${offer.title}
                      </span>`
              )
              .join(""); // Join all offers into a single string
      }
  
      // Initialize price HTML
      let priceHTML = "";
  
      // Render initial price if available
      if (room.initialPrice && !isNaN(parseFloat(room.initialPrice))) {
          priceHTML += `<span class="initial-price">
              ${parseFloat(room.initialPrice).toFixed(2)} €
          </span>`;
      }
  
      // Render final price
      const price = parseFloat(room.price); // Ensure price is a number
      if (!isNaN(price)) {
          priceHTML += `<span class="final-price">
              ${price.toFixed(2)} €
          </span>`;
      } else {
          priceHTML += `<span class="final-price">
              N/A €
          </span>`;
      }
    
      // Return both HTML parts as an object
      return {
          specialOffersHTML,
          priceHTML,
      };
  };
  
    
    const formattedCurrentDate = computed(() => {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`; // Format YYYY-MM-DD
});

   const openDatepicker = (event) => {
      if (event.target.showPicker) {
        event.target.showPicker();
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
      boardTypes,
      getOrdinalSuffix,
      priceRange,
      selectedPrice,
      showDatepicker,
      getAvailabilityClass,
      getAvailabilityLabel,
      renderRoomDetails,
      formattedCurrentDate, 
      openDatepicker
    };
  },
}).mount("#brosSearchApp");
