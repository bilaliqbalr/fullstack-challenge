<script>
import UserCard from "./UserCard.vue";
import { useDisplay } from 'vuetify'

export default {
    components: {UserCard},
    data: () => ({
        apiResponse: null,
        cols: 3,
    }),
    mounted () {
        const display = useDisplay()
        this.cols = display.thresholds.mobile ? 12 : 4
    },
    created() {
        this.fetchData()
    },
    methods: {
        async fetchData() {
            const url = 'http://localhost/'
            this.apiResponse = await (await fetch(url)).json()
        }
    }
}
</script>

<template>
  <div v-if="!apiResponse">
    Pinging the api...
  </div>

  <div v-else>
    <v-row>
        <v-col v-for="(user, key) in apiResponse.users" :key="key" :cols="cols">
            <user-card :user="user"></user-card>
        </v-col>
    </v-row>
  </div>
</template>