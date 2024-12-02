<div id="brosSearchApp" class="bros-search-app">
<form @submit.prevent="loadAvailableProperties" class="bros-search-form">
    <!-- First column -->
    <div class="search-column first-column">
        <div class="input-wrapper">
            <span class="input-label">Destination</span>
            <input
                id="inputSearch"
                v-model="searchQuery"
                type="text"
                placeholder="Typing destination..."
                @focus="showList"
                @blur="hideList"
                
            />
            <ul class="select-list" v-if="isListVisible && searchResults.length > 0">
                <li
                    v-for="(result, index) in searchResults"
                    :key="index"
                    @click="selectDestination(result)"
                >
                    {{ result }}
                </li>
            </ul>
        </div>
        <div class="row-wrapper">
            <div class="input-wrapper ">
                <span class="input-label">Check-in</span>
                <input v-model="checkinDate" class="padding-style" type="date" placeholder="dd-mm-yyyy">
            </div>
            <div class="input-wrapper ">
                <span class="input-label ">Nights</span>
                <select class="padding-style" v-model="nightsCount">
                    <option v-for="n in 60" :key="n" :value="n">{{ n }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Second column -->
    <div class="search-column second-column">
    <!-- Rooms selector -->
    <div class="input-wrapper">
        <span class="input-label">Rooms</span>
        <select v-model="roomsCount" @change="updateRooms">
            <option v-for="r in 10" :key="r" :value="r">{{ r }}</option>
        </select>
    </div>

    <!-- Dynamic rows for each room -->
    <div v-for="(room, index) in rooms" :key="index" class="row-wrapper">
        <span class="input-label room-span">ROOM {{ index + 1 }}</span>
        <div class="input-wrapper">
            <span class="input-label">Adults</span>
            <select v-model="room.adults" class="border-style">
                <option v-for="a in 10" :key="a" :value="a">{{ a }}</option>
            </select>
        </div>
        <div class="input-wrapper">
            <span class="input-label">Children</span>
            <select v-model="room.children" class="border-style">
                <option :value="0" key="0">0</option>
                <option v-for="c in 10" :key="c" :value="c">{{ c }}</option>
            </select>
        </div>
    </div>

  </div>


    <!-- Submit button -->
    <div class="button-row">
        <button type="submit" class="search-form-btn">Search</button>
    </div>
</form>


        <!-- FILTERS section -->
        <div>
            <button class="filters-button" v-if="searchResultsLP.length > 0" @click="toggleFilters">
                {{ showFilters ? 'Hide filters' : 'Show filters' }}
            </button>    
        </div>
        
        <div class="filters-wrapper" :style="{ display: showFilters ? 'block' : 'none' }">
            <div class="filters-row">
                <div class="filter-section">
                    <h5>Filter by rating</h5>
                    <div class="rating-filters">
                        <label v-for="rating in [5, 4, 3, 2, 1]" :key="rating" class="rating-filter">
                            <input
                                type="checkbox"
                                :value="rating"
                                v-model="selectedRatings"
                                @change="applyFilters"
                            />
                            <span v-html="generateStars(rating)"></span>
                        </label>
                    </div>
                </div>
                <div class="filter-section">
                    <h5>Filter by accomodation type</h5>
                    <div class="accomodation-filters">
                        <label v-for="type in accommodationTypes" :key="type.value" class="type-filter">
                            <input
                                type="checkbox"
                                :value="type.value"
                                v-model="selectedTypes"
                                @change="applyFilters"
                            />
                            {{ type.label }}                          
                        </label>
                    </div>
                </div>
                <div class="filter-section">
                    <h5>Filter by Availability</h5>
                    <div class="accomodation-filters">
                        <label v-for="type in availableTypes" :key="type.value" class="type-filter">
                            <input
                                type="checkbox"
                                :value="type.value"
                                v-model="selectedAvailable"
                                @change="applyFilters"
                            />
                            {{ type.label }}                          
                        </label>
                    </div>
                </div>
                <div class="filter-section">
                    <h5>Filter by Board</h5>
                    <div class="accomodation-filters">
                        <label v-for="type in boardTypes" :key="type.value" class="type-filter">
                            <input
                                type="checkbox"
                                :value="type.value"
                                v-model="selectedBoard"
                                @change="applyFilters"
                            />
                            {{ type.label }}                          
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search text -->
        <div class="search-header">
            <h2 class="search-title">{{ searchStatus }}</h2>
        </div>
  <!-- LOAD DATA section -->
        <div>
            <div v-if="filteredResultsLP.length > 0" class="results-section">
                <!-- Iterate over filtered results -->
                <div v-for="(result, index) in filteredResultsLP" :key="index" class="result-item">
                    <!-- Prvi red: Property image and details -->
                    <div class="property-details-row">
                        <div class="property-image">
                            <a :href="`https://bros-travel.com/property/${result.property_id}/`" target="_blank">
                                <img :src="`https://services.bros-travel.com/images/properties/${result.property_id}/${result.property_image}`" :alt="result.property_name" />
                            </a>
                        </div>
                        <div class="text-data">
                            <div class="property-name">
                                <a :href="`https://bros-travel.com/property/${result.property_id}/`" target="_blank">
                                    <h3>{{ result.property_name }}</h3>
                                </a>
                            </div>
                            <div class="property-rating">
                                <div v-if="result.property_rating" class="stars">
                                    <!-- Generate stars dynamically based on property rating -->
                                    <span v-html="generateStars(result.property_rating)"></span>
                                </div>
                            </div>
                            <div class="property-location">
                                {{ result.location_name }}, {{ result.subregion }}, {{ result.region }}, {{ result.country }}
                            </div>
                            <div class="property-description">
                                {{ result.property_description }}
                            </div>
                        </div>
                    </div>

                    <!-- Drugi red: Rooms table -->
                    <div class="rooms-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tip sobe</th>
                                    <th>Dostupnost</th>
                                    <th>Max osoba</th>
                                    <th>Pansion</th>
                                    <th>Cena (€)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(room, roomIndex) in result.rooms" :key="roomIndex">
                                    <td data-label="Tip sobe">{{ room.type }}</td>
                                    <td data-label="Dostupnost">{{ room.available }}</td>
                                    <td data-label="Max osoba">{{ room.max }}</td>
                                    <td data-label="Pansion">{{ room.board }}</td>
                                    <td data-label="Cena (€)">{{ room.price }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
 
</div>

</div>