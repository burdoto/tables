/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'
import { getCurrentUser } from '@nextcloud/auth'
import debounce from 'debounce'
import { showError } from '@nextcloud/dialogs'
import '@nextcloud/dialogs/style.css'
import ShareTypes from './shareTypesMixin.js'
import generalHelper from './generalHelper.js'

export default {
	mixins: [ShareTypes, generalHelper],
	data() {
		return {
			query: '',
			loading: false,
			minSearchStringLength: 1,
			maxAutocompleteResults: 20,
			suggestions: [],
			recommendations: [],
			currentUserId: getCurrentUser().uid,
		}
	},
	computed: {
		isValidQuery() {
			return this.query?.trim() && this.query.length >= this.minSearchStringLength
		},
		options() {
			if (this.isValidQuery) {
				return this.suggestions
			}
			return this.recommendations
		},

		noResultText() {
			if (this.loading) {
				return t('tables', 'Searching …')
			}
			return t('tables', 'No elements found.')
		},

	},
	methods: {
		getShareTypes() {
			const types = []
			if (this.selectUsers) {
				types.push(this.SHARE_TYPES.SHARE_TYPE_USER)
			}
			if (this.selectGroups) {
				types.push(this.SHARE_TYPES.SHARE_TYPE_GROUP)
			}
			if (this?.selectTeams) {
				types.push(this.SHARE_TYPES.SHARE_TYPE_CIRCLE)
			}
			return types
		},
		getShareTypeString() {
			const strings = []
			if (this.selectUsers) {
				strings.push('user')
			}
			if (this.selectGroups) {
				strings.push('group')
			}
			if (this?.selectTeams) {
				strings.push('team')
			}
			return strings.join(' or ')
		},
		getPlaceholder() {
			return t('tables', '{shareTypeString}...', { shareTypeString: this.getShareTypeString() })
		},
		async asyncFind(query) {
			this.query = query.trim()
			if (this.isValidQuery) {
				this.loading = true
				await this.debounceGetSuggestions(query)
			}
		},
		getType(source) {
			if (source.startsWith('users')) {
				return 0
			} else if (source.startsWith('circles')) {
				return 2
			} else if (source.startsWith('groups')) {
				return 1
			} else {
				showError(t('tables', 'Unsupported source: {source}', { source }))
				throw new Error('Unsupported source: ' + source)
			}
		},
		async getSuggestions(search) {
			this.loading = true
			const shareTypes = this.getShareTypes()
			let shareTypeQueryString = ''
			shareTypes.forEach(shareType => {
				shareTypeQueryString += `&shareTypes[]=${shareType}`
			})
			const url = generateOcsUrl(`core/autocomplete/get?search=${search}${shareTypeQueryString}&limit=${this.maxAutocompleteResults}`)

			try {
				const res = await axios.get(url)
				const rawSuggestions = res.data.ocs.data.map(result => {
					return this.formatResult(result)
				})
				this.suggestions = this.filterOutUnwantedItems(rawSuggestions)
				this.loading = false
			} catch (err) {
				console.debug(err)
				showError(t('tables', 'Failed to fetch {shareTypeString}', { shareTypeString: this.getShareTypeString().toLowerCase() }))
			}
		},

		debounceGetSuggestions: debounce(function(...args) {
			this.getSuggestions(...args)
		}, 300),

	},
}
