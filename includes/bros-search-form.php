<div id="brosSearchApp" class="bros-search-app">
        <form @submit.prevent="loadLocationsProperties" class="bros-search-form">
            <!-- First column: Destination and Check-in -->
            <div class="search-column">
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
                    
                    <!-- Show the list if isListVisible is true and searchResults have data -->
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
                <div class="input-wrapper">
                    <span class="input-label">Check in</span>
                    <input type="date" placeholder="dd/mm/yyyy">
                </div>
            </div>

            <!-- Second column: Nights and Rooms -->
            <div class="search-column">
                <div class="input-wrapper">
                    <span class="input-label">Nights</span>
                    <select v-model="nightsCount">
                        <option v-for="n in 60" :key="n" :value="n">{{ n }}</option>
                    </select>
                </div>
                <div class="input-wrapper">
                    <span class="input-label">Rooms</span>
                    <select>
                        <option v-for="r in 10" :key="r">{{ r }}</option>
                    </select>
                </div>
            </div>

            <!-- Third column: Adults and Children -->
            <div class="search-column">  
                <!-- <span class="room-span">Room 1</span>       -->
                <div class="input-wrapper">            
                    <span class="input-label room-label">Adults</span>
                    <select v-model="adultsCount">
                        <option v-for="a in 10" :key="a" :value="a">{{ a }}</option>
                    </select>
                </div>

                <div class="input-wrapper">
                    <span class="input-label">Children</span>
                    <select>
                        <option v-for="c in 10" :key="c">{{ c }}</option>
                    </select>
                </div>
            </div>

        <!-- Submit button for form submission -->
            <button type="submit">Search</button>
        </form>


        <!-- Filters section -->
        <div>
            <button class="filters-button" v-if="searchResultsLP.length > 0" @click="toggleFilters">
                {{ showFilters ? 'Hide filters' : 'Show filters' }}
            </button>    
        </div>
        
        <div class="filters-wrapper" :style="{ display: showFilters ? 'block' : 'none' }">
            <h5>Filter by rating</h5>
            <div class="rating-filters">
                <label v-for="rating in [5, 4, 3, 2, 1]" :key="rating" class="rating-filter">
                    <input
                        type="checkbox"
                        :value="rating"
                        v-model="selectedRatings"
                        @change="filterByRating"
                    />
                    <span v-html="generateStars(rating)"></span>
                </label>
            </div>
        </div>
        <!-- Search text -->
        <div class="search-header">
            <h2 class="search-title">{{ searchStatus }}</h2>
        </div>
       <!-- Load data section -->
        <div v-if="(filteredResultsLP.length > 0 || searchResultsLP.length > 0)" class="results-section">
            <div
                v-for="(result, index) in (selectedRatings.length > 0 ? filteredResultsLP : searchResultsLP)"
                :key="index"
                class="result-item"
            >
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
                        {{ result.location_name }}, {{ result.region }}, {{ result.subregion }}, {{ result.country }}
                    </div>
                    <div class="property-description">
                        {{ result.property_description }}
                    </div>
                </div>

            </div>
        </div>
    </div>