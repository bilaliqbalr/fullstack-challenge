<script>
export default {
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    computed: {
        forecast() {
            return this.user.forecast
        },
        forecastTicks() {
            let obj = {};
            for (let i in this.forecast) {
                obj[i] = this.forecast[i].number
            }
            return obj
        },
        latestForecast() {
            return this.forecast[this.idx]
        }
    },
    data: () => ({
        showModal: false,
        showFahrenheit: false,
        idx: 0,
    }),
    methods: {
        getCelcius(temp) {
            return ((temp - 32) * 5/9).toFixed(1)
        }
    },
}
</script>

<template>
    <v-dialog
        v-model="showModal"
        persistent
        width="auto"
    >
        <template v-slot:activator="{ props }">
            <v-btn
                color="primary"
                v-bind="props"
            >
                Full Report
            </v-btn>
        </template>

        <v-card class="mx-auto" min-width="368" max-width="368">
            <v-card-item>
                <v-card-title class="text-h5">{{ latestForecast.name }}</v-card-title>

                <v-card-subtitle>
                    <v-icon
                        icon="mdi-weather-cloudy-clock"
                        size="18"
                        color="info"
                        class="mr-1 pb-1"
                    ></v-icon>

                    {{ latestForecast.shortForecast }}
                </v-card-subtitle>
            </v-card-item>

            <v-card-text class="py-0">
                <v-row align="center" hide-gutters no-gutters>
                    <v-col
                        class="text-h3"
                        cols="6"
                    >
                        <span class="temperature" @click="showFahrenheit = !showFahrenheit" v-if="showFahrenheit">{{ getCelcius(latestForecast.temperature) }}°C</span>
                        <span class="temperature" @click="showFahrenheit = !showFahrenheit" v-else>{{ (latestForecast.temperature).toFixed(1) }}F</span>
                    </v-col>

                    <v-col cols="6" class="text-right">
                        <v-icon size="88" color="error" icon="mdi-weather-hurricane"></v-icon>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-list-item density="compact">
                <template v-slot:prepend>
                    <v-icon icon="mdi-weather-windy"></v-icon>
                </template>

                <v-list-item-title>{{ latestForecast.windSpeed }}</v-list-item-title>
            </v-list-item>

            <v-card-text>{{ latestForecast.detailedForecast }}</v-card-text>

            <v-expand-transition>
                <div>
                    <v-slider
                        v-model="idx"
                        :max="Object.keys(forecastTicks).length"
                        :step="1"
                        :ticks="forecastTicks"
                        class="mx-4"
                        color="primary"
                        density="compact"
                        hide-details
                        show-ticks="always"
                        thumb-size="10"
                        style='min-height:50px'
                    ></v-slider>
                </div>
            </v-expand-transition>
            <v-card-actions>
                <v-btn color="primary" block @click="showModal = false">Close</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

</template>

<style scoped>

</style>