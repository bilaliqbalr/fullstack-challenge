<script setup lang="ts">
import { RouterLink, RouterView } from "vue-router";
</script>

<template>
    <v-app>
        <!-- Fixed header -->
        <v-app-bar app color="primary" dark>
            <v-toolbar-title>Weather Forecast</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-menu offset-y>
                <template #activator="{ on, attrs }">
                    <v-btn icon v-bind="attrs" v-on="on">
                        <v-icon>mdi-dots-vertical</v-icon>
                    </v-btn>
                </template>
                <v-list>
                    <v-list-item to="/" router>
                        <v-list-item-title>Home</v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>
        </v-app-bar>

        <!-- Body content -->
        <v-main>
            <v-container>
                <RouterView />
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>

</style>
