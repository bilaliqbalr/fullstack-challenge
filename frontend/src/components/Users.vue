<script>
import UserCard from "./UserCard.vue";
import { useDisplay } from 'vuetify'

export default {
    components: {UserCard},
    data: () => ({
        apiResponse: null,
        cols: 3,
        page: 1,
        pagination: {},
    }),
    mounted () {
        const display = useDisplay()
        this.cols = display.thresholds.mobile ? 12 : 4
    },
    created() {
        this.fetchData()
    },
    watch: {
        page (newValue) {
            this.fetchData()
        }
    },
    methods: {
        async fetchData() {
            this.apiResponse = false;

            const url = 'http://localhost/?page=' + this.page
            let response = await (await fetch(url)).json()
            this.apiResponse = response.data
            this.pagination = response.meta;
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
        <v-col v-for="(user, key) in apiResponse" :key="key" :cols="cols">
            <user-card :user="user"></user-card>
        </v-col>
    </v-row>
    <v-pagination v-model="page" class="my-4" :length="Math.ceil(pagination.total / pagination.per_page)"></v-pagination>
  </div>
</template>