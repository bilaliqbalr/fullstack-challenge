<script>
import ForecastDetail from "./ForecastDetail.vue";
export default {
    name: "UserCard",
    components: {ForecastDetail},
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    computed: {
        forecast() {
            return this.user.forecast[0]
        },
    },
    data: () => ({
        showFahrenheit: false,
    }),
    methods: {
        getCelcius(temp) {
            return ((temp - 32) * 5/9).toFixed(1)
        }
    },
}
</script>

<template>
    <v-card class="mx-auto" min-height="160">
        <v-card-title>{{ user.name }}</v-card-title>
        <template v-if="user.forecast.length > 0">
            <v-card-text>
                {{ user.forecast[0].shortForecast }}
                <br>
                Temperature:
                <span class="temperature" @click="showFahrenheit = !showFahrenheit" v-if="showFahrenheit">{{ getCelcius(forecast.temperature) }}°C</span>
                <span class="temperature" @click="showFahrenheit = !showFahrenheit" v-else>{{ (forecast.temperature).toFixed(1) }}F</span>
            </v-card-text>
            <v-card-actions>
                <forecast-detail :user="user"></forecast-detail>
            </v-card-actions>
        </template>
        <template v-else>
            <v-card-text>
                <v-icon
                    icon="mdi-alert"
                    size="18"
                    color="error"
                    class="mr-1 pb-1"
                ></v-icon>
                Unable to get weather info because API only provide data for USA only
            </v-card-text>
        </template>
    </v-card>
</template>

<style>
.temperature {
    cursor: pointer;
    border-bottom: 1px dotted #000;
}
</style>