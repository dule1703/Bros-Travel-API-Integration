<div id="brosSearchApp" class="bros-search-app">
<form @submit.prevent="loadAvailableProperties" class="bros-search-form">
    <!-- First column -->
    <div class="search-column first-column">
        <div class="input-wrapper">
            <span class="input-label">Destinacija</span>
            <input
                id="inputSearch"
                v-model="searchQuery"
                type="text"
                placeholder="Unesite destinaciju..."
                @focus="showList"
                @blur="hideList"
                required
            />
            <ul class="select-list" v-if="isListVisible && searchResults.length > 0">
                <li
                v-for="(result, index) in searchResults"
                :key="index"
                @click="selectDestination(result.text)"
                >
                <img :src="result.icon" alt="Destination icon" class="destination-icon" />
                <span>{{ result.text }}</span>
                </li>
            </ul>
        </div>

        <div class="row-wrapper">
           <div class="input-wrapper">
    <span class="input-label">Datum prijave</span>
    <input
  v-model="checkinDate"
  type="date"
  :min="formattedCurrentDate"
  required
  @click="openDatepicker"
/>
</div>
            <div class="input-wrapper ">
                <span class="input-label ">Broj noćenja</span>
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
        <span class="input-label">Broj soba</span>
        <select v-model="roomsCount" @change="updateRooms">
            <option v-for="r in 10" :key="r" :value="r">{{ r }}</option>
        </select>
    </div>

    <!-- Dynamic rows for each room -->
    <div v-for="(room, index) in rooms" :key="index" class="room-wrapper">
        <div class="row-wrapper">
            <span class="input-label room-span">SOBA {{ index + 1 }}</span>
            
            <!-- Adults Input -->
            <div class="input-wrapper">
                <span class="input-label">Odrasli</span>
                <select v-model="room.adults" class="border-style">
                    <option v-for="a in 10" :key="a" :value="a">{{ a }}</option>
                </select>
            </div>
            
            <!-- Children Input -->
            <div class="input-wrapper">
                <span class="input-label">Deca</span>
                <select v-model="room.children" class="border-style" @change="updateRooms">
                    <option :value="0" key="0">0</option>
                    <option v-for="c in 10" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>
        </div>

        <!-- Child Age Inputs -->
        <div v-if="room.children > 0" class="child-age-wrapper">
            <div v-for="(age, childIndex) in room.childAges" :key="`child-age-${index}-${childIndex}`" class="child-age-row">
                <label class="child-age-label">
                    {{ childIndex + 1 }}{{ getOrdinalSuffix(childIndex + 1) }} child age:
                </label>
                <div class="child-age-wrapper">
                    <span class="input-label">Godine</span>
                    <select v-model="room.childAges[childIndex]" class="border-style">                       
                        <option v-for="ca in 17" :key="ca" :value="ca">{{ ca }}</option>
                    </select>
                </div>
               
            </div>
        </div>
    </div>
</div>



    <!-- Submit button -->
    <div class="button-row">
        <button type="submit" class="search-form-btn">Pretražite</button>
    </div>
</form>


        <!-- FILTERS section -->
        <div>
            <button class="filters-button" v-if="searchResultsLP.length > 0" @click="toggleFilters">
                {{ showFilters ? 'Sakrijte filtere' : 'Prikažite filtere' }}
            </button>    
        </div>
        
        <div class="filters-wrapper" :style="{ display: showFilters ? 'block' : 'none' }">
            <div class="filters-row">
                <div class="filter-section">
                    <h5>Filter po rejtingu</h5>
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
                    <h5>Filter po tipu smeštaja</h5>
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
                    <h5>Filter po dostupnosti</h5>
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
                    <h5>Filter po pansionu</h5>
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
                <div class="filter-section">
                    <h5>Filter po ceni</h5>
                    <div class="price-slider">
                        <label class="price-label">Min: {{ selectedPrice.min }} €</label>
                        <input
                            type="range"
                            v-model="selectedPrice.min"
                            :min="priceRange.min"
                            :max="priceRange.max"
                            step="1"
                            @input="applyFilters"
                        />
                        <label class="price-label">Max: {{ selectedPrice.max }} €</label>
                        <input
                            type="range"
                            v-model="selectedPrice.max"
                            :min="priceRange.min"
                            :max="priceRange.max"
                            step="1"
                            @input="applyFilters"
                        />
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
                                    <th>Cena</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(room, roomIndex) in result.rooms" :key="roomIndex">
                                    <td data-label="Tip sobe">{{ room.type }}</td>
                                    <td data-label="Dostupnost">
                                        <span v-html="renderRoomDetails(room).specialOffersHTML"></span>
                                        <span :class="getAvailabilityClass(room.available)">
                                            {{ getAvailabilityLabel(room.available) }}
                                        </span>
                                    </td>
                                    <td data-label="Max osoba">{{ room.max }}</td>
                                    <td data-label="Pansion">{{ room.board }}</td>
                                    <td data-label="Cena">
                                        <span v-html="renderRoomDetails(room).priceHTML"></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
 
</div>

</div>