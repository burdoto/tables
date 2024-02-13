let localUser
const columnTitle = 'check'
const tableTitle = 'Test selection check'

describe('Test column ' + columnTitle, () => {

	before(function() {
		cy.createRandomUser().then(user => {
			localUser = user
			cy.login(localUser)
			cy.visit('apps/tables')
			cy.createTable(tableTitle)
		})
	})

	beforeEach(function() {
		cy.login(localUser)
		cy.visit('apps/tables')
	})

	it('Insert and test rows - default value unchecked', () => {
		cy.loadTable(tableTitle)
		cy.createSelectionCheckColumn(columnTitle, null, true)

		// insert row
		cy.get('button').contains('Create row').click()
		cy.get('button').contains('Save').click()
		cy.get('.custom-table table tr td div .material-design-icon.radiobox-blank-icon').should('be.visible')

		// insert row
		cy.get('button').contains('Create row').click()
		cy.get('[data-cy="selectionCheckFormSwitch"]').first().click({ force: true })
		cy.get('button').contains('Save').click()
		cy.get('.custom-table table tr td div .material-design-icon.check-circle-outline-icon').should('be.visible')

		cy.removeColumn(columnTitle)
	})

	it('Insert and test rows - default value checked', () => {
		cy.loadTable(tableTitle)
		cy.createSelectionCheckColumn(columnTitle, true, true)

		// insert row
		cy.get('button').contains('Create row').click()
		cy.get('button').contains('Save').click()
		cy.get('.custom-table table tr td div .material-design-icon.check-circle-outline-icon').should('be.visible')

		// insert row
		cy.get('button').contains('Create row').click()
		cy.get('[data-cy="selectionCheckFormSwitch"]').first().click({ force: true })
		cy.get('button').contains('Save').click()
		cy.get('.custom-table table tr td div .material-design-icon.radiobox-blank-icon').should('be.visible')

		cy.removeColumn(columnTitle)
	})

})
