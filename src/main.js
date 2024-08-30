/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import Vue from 'vue'
import App from './App.vue'
import Vuex from 'vuex'
import store from './store/store.js'
import router from './router.js'
import VuePapaParse from 'vue-papa-parse'

Vue.mixin({ methods: { t, n } })
Vue.use(Vuex)
Vue.use(VuePapaParse)

export default new Vue({
	el: '#content',
	router,
	store,
	render: h => h(App),
})
