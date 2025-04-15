<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mb-8">
    <!-- Header with toggle button -->
    <div 
      class="p-4 sm:p-6 cursor-pointer flex items-center justify-between"
      @click="toggleFilters"
    >
      <div class="flex items-center space-x-3">
        <h3 class="text-base sm:text-lg font-semibold text-text-light dark:text-text-dark">Advanced Filters</h3>
        <span 
          v-if="hasActiveFilters" 
          class="px-2 py-1 text-xs font-medium bg-primary/10 dark:bg-dark-primary/10 text-primary dark:text-dark-primary rounded-full"
        >
          {{ activeFiltersCount }} active
        </span>
      </div>
      <div class="flex items-center space-x-4">
        <button
          v-if="hasActiveFilters"
          @click.stop="clearFilters"
          class="text-xs sm:text-sm text-primary dark:text-dark-primary hover:underline flex items-center space-x-1"
        >
          <span>Clear all</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <svg 
          xmlns="http://www.w3.org/2000/svg" 
          class="h-5 w-5 text-gray-500 dark:text-gray-400 transition-transform duration-200"
          :class="{ 'transform rotate-180': isExpanded }"
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>

    <!-- Collapsible content -->
    <div 
      class="overflow-hidden transition-all duration-200 ease-in-out"
      :class="{ 'max-h-0': !isExpanded, 'max-h-[1000px]': isExpanded }"
    >
      <div class="p-4 sm:p-6 pt-0 space-y-4 sm:space-y-6">
        <!-- Date Range -->
        <div>
          <label class="inline-flex items-center text-sm font-medium text-text-light dark:text-text-dark mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Date Range
          </label>
          <div class="relative">
            <Datepicker
              v-model="dateRange"
              range
              :enable-time-picker="false"
              :format="'MMM dd, yyyy'"
              :locale="locale"
              class="w-full"
              :placeholder="'Select date range'"
              menuClassName="right-0"
              :dark="false"
              textColor="#FF5A5F"
              :autoApply="true"
              :preview="true"
              :highlightWeekends="true"
              :arrowNavigation="true"
              :transitions="true"
              :partial-range="false"
              :inputClassName="{
                'dp__input': true,
                'dp__input_focus': true,
                'dp__input_state_active': true,
                'dp__input_state_hover': true,
                'border border-gray-300 dark:border-gray-600': true,
                'rounded-lg pr-10': true,
                'focus:ring-2 focus:ring-primary dark:focus:ring-dark-primary focus:border-transparent': true,
              }"
              :hideInputIcon="true"
            />
            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-primary dark:text-dark-primary pointer-events-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div v-if="dateRange" class="mt-1.5 text-xs text-primary dark:text-dark-primary">
              {{ dateRange[0].toLocaleDateString() }} - {{ dateRange[1].toLocaleDateString() }}
            </div>
          </div>
        </div>

        <!-- Location -->
        <div>
          <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
            Location
          </label>
          <div class="flex space-x-2">
            <input
              v-model="location"
              type="text"
              placeholder="Enter location"
              class="flex-1 px-3 sm:px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-text-light dark:text-text-dark focus:ring-2 focus:ring-primary dark:focus:ring-dark-primary focus:border-transparent text-sm sm:text-base"
            />
            <button
              @click="getCurrentLocation"
              class="px-3 sm:px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-text-light dark:text-text-dark hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
              :disabled="isGettingLocation"
            >
              <svg
                v-if="isGettingLocation"
                class="animate-spin h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
            </button>
          </div>
        </div>

        <!-- Categories -->
        <div>
          <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
            Categories
          </label>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="category in categories"
              :key="category"
              @click="toggleCategory(category)"
              class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-medium transition-colors"
              :class="[
                selectedCategories.includes(category)
                  ? 'bg-primary dark:bg-dark-primary text-white'
                  : 'bg-gray-100 dark:bg-gray-700 text-text-light dark:text-text-dark hover:bg-gray-200 dark:hover:bg-gray-600'
              ]"
            >
              {{ category }}
            </button>
          </div>
        </div>

        <!-- Distance Filter -->
        <div>
          <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
            Distance (km)
          </label>
          <input
            type="range"
            v-model="distance"
            min="0"
            max="100"
            step="1"
            class="w-full"
          />
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ distance }} km
          </div>
        </div>

        <!-- Popularity/Rating Filter -->
        <div>
          <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
            Popularity/Rating
          </label>
          <select
            v-model="popularity"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-text-light dark:text-text-dark focus:ring-2 focus:ring-primary dark:focus:ring-dark-primary focus:border-transparent text-sm"
          >
            <option value="">Select</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
        </div>

        <!-- Quick Date Presets -->
        <div>
          <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
            Quick Date Presets
          </label>
          <div class="flex space-x-2">
            <button
              v-for="preset in datePresets"
              :key="preset.label"
              @click="applyDatePreset(preset.range as [Date, Date])"
              class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-text-light dark:text-text-dark hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm"
            >
              {{ preset.label }}
            </button>
          </div>
        </div>

        <!-- Search-as-You-Type for Categories -->
        <div>
          <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
            Search Categories
          </label>
          <input
            v-model="categorySearch"
            type="text"
            placeholder="Search categories"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-text-light dark:text-text-dark focus:ring-2 focus:ring-primary dark:focus:ring-dark-primary focus:border-transparent text-sm"
          />
          <div class="flex flex-wrap gap-2 mt-2">
            <button
              v-for="category in filteredCategories"
              :key="category"
              @click="toggleCategory(category)"
              class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-medium transition-colors"
              :class="[
                selectedCategories.includes(category)
                  ? 'bg-primary dark:bg-dark-primary text-white'
                  : 'bg-gray-100 dark:bg-gray-700 text-text-light dark:text-text-dark hover:bg-gray-200 dark:hover:bg-gray-600'
              ]"
            >
              {{ category }}
            </button>
          </div>
        </div>

        <!-- Improved Price Range Slider -->
        <div>
          <label class="inline-flex items-center text-sm font-medium text-text-light dark:text-text-dark mb-2">
            Price Range
          </label>
          <div class="flex items-center justify-between mt-2">
            <div class="flex items-center w-[45%]">
              <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">$</span>
              <input
                type="number"
                v-model.number="priceRange[0]"
                min="0"
                max="1000"
                placeholder="Min"
                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-text-light dark:text-text-dark focus:ring-2 focus:ring-primary dark:focus:ring-dark-primary focus:border-transparent text-sm"
                @change="validatePriceInput(0)"
              />
            </div>
            <div class="text-gray-500 dark:text-gray-400">to</div>
            <div class="flex items-center w-[45%]">
              <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">$</span>
              <input
                type="number"
                v-model.number="priceRange[1]"
                min="0"
                max="1000"
                placeholder="Max"
                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-text-light dark:text-text-dark focus:ring-2 focus:ring-primary dark:focus:ring-dark-primary focus:border-transparent text-sm"
                @change="validatePriceInput(1)"
              />
            </div>
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Selected Range: ${{ priceRange[0] }} - ${{ priceRange[1] }}
          </div>
        </div>

        <!-- Apply Filters Button -->
        <button
          @click="applyFilters"
          class="w-full py-2 px-4 bg-primary dark:bg-dark-primary text-white rounded-lg hover:bg-primary-dark dark:hover:bg-dark-primary-dark transition-colors text-sm sm:text-base"
          :disabled="!hasActiveFilters"
        >
          Apply Filters
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
// Remove slider import since we're not using it anymore
import { useGeolocation } from '@vueuse/core';

interface Props {
  categories: string[];
  initialFilters?: {
    dateRange?: [Date, Date] | null;
    location?: string;
    selectedCategories?: string[];
    priceRange?: [number, number];
  };
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'apply-filters', filters: {
    dateRange: [Date, Date] | null;
    location: string;
    selectedCategories: string[];
    priceRange: [number, number];
  }): void;
}>();

const { coords, locatedAt, error: locationError } = useGeolocation();
const isGettingLocation = ref(false);
const isExpanded = ref(false);

const dateRange = ref<[Date, Date] | null>(props.initialFilters?.dateRange || null);
const location = ref(props.initialFilters?.location || '');
const selectedCategories = ref<string[]>(props.initialFilters?.selectedCategories || []);
const priceRange = ref<[number, number]>(props.initialFilters?.priceRange || [0, 1000]);

const locale = 'en';

const hasActiveFilters = computed(() => {
  return dateRange.value !== null ||
    location.value !== '' ||
    selectedCategories.value.length > 0 ||
    priceRange.value[0] !== 0 ||
    priceRange.value[1] !== 1000;
});

const activeFiltersCount = computed(() => {
  let count = 0;
  if (dateRange.value !== null) count++;
  if (location.value !== '') count++;
  if (selectedCategories.value.length > 0) count++;
  if (priceRange.value[0] !== 0 || priceRange.value[1] !== 1000) count++;
  return count;
});

const toggleFilters = () => {
  isExpanded.value = !isExpanded.value;
};

const toggleCategory = (category: string) => {
  const index = selectedCategories.value.indexOf(category);
  if (index === -1) {
    selectedCategories.value.push(category);
  } else {
    selectedCategories.value.splice(index, 1);
  }
};

const getCurrentLocation = async () => {
  isGettingLocation.value = true;
  try {
    const position = await new Promise<GeolocationPosition>((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject);
    });
    
    // Convert coordinates to address using reverse geocoding
    const response = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`
    );
    const data = await response.json();
    location.value = data.display_name;
  } catch (error) {
    console.error('Error getting location:', error);
  } finally {
    isGettingLocation.value = false;
  }
};

const validatePriceInput = (index: number) => {
  // Ensure values are numbers and within range
  if (isNaN(priceRange.value[index]) || priceRange.value[index] === null) {
    priceRange.value[index] = index === 0 ? 0 : 1000;
  }
  
  if (priceRange.value[index] < 0) {
    priceRange.value[index] = 0;
  } else if (priceRange.value[index] > 1000) {
    priceRange.value[index] = 1000;
  }
  
  // Ensure min <= max
  if (index === 0 && priceRange.value[0] > priceRange.value[1]) {
    priceRange.value[0] = priceRange.value[1];
  } else if (index === 1 && priceRange.value[1] < priceRange.value[0]) {
    priceRange.value[1] = priceRange.value[0];
  }
};

const clearFilters = () => {
  dateRange.value = null;
  location.value = '';
  selectedCategories.value = [];
  priceRange.value = [0, 1000];
};

const applyFilters = () => {
  emit('apply-filters', {
    dateRange: dateRange.value,
    location: location.value,
    selectedCategories: selectedCategories.value,
    priceRange: priceRange.value,
  });
};

// Watch for prop changes to update filters
watch(() => props.initialFilters, (newVal) => {
  if (newVal) {
    dateRange.value = newVal.dateRange || null;
    location.value = newVal.location || '';
    selectedCategories.value = newVal.selectedCategories || [];
    priceRange.value = newVal.priceRange || [0, 1000];
  }
}, { deep: true });

const distance = ref(50); // Default distance in km
const popularity = ref('');
const categorySearch = ref('');

interface DatePreset {
  label: string;
  range: [Date, Date];
}

const datePresets: DatePreset[] = [
  { label: 'Today', range: [new Date(), new Date()] },
  { label: 'This Weekend', range: [getNextSaturday(), getNextSunday()] },
  { label: 'Next Week', range: [getNextMonday(), getNextSunday()] },
];

const filteredCategories = computed(() => {
  return props.categories.filter(category =>
    category.toLowerCase().includes(categorySearch.value.toLowerCase())
  );
});

const applyDatePreset = (range: [Date, Date]) => {
  dateRange.value = range;
};

function getNextSaturday() {
  const today = new Date();
  const day = today.getDay();
  const diff = 6 - day;
  return new Date(today.getFullYear(), today.getMonth(), today.getDate() + diff);
}

function getNextSunday() {
  const saturday = getNextSaturday();
  return new Date(saturday.getFullYear(), saturday.getMonth(), saturday.getDate() + 1);
}

function getNextMonday() {
  const today = new Date();
  const day = today.getDay();
  const diff = (8 - day) % 7;
  return new Date(today.getFullYear(), today.getMonth(), today.getDate() + diff);
}
</script>