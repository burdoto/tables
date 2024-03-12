<?php

namespace OCA\Tables\Controller;

use Exception;
use OCA\Tables\Errors\InternalError;
use OCA\Tables\Errors\NotFoundError;
use OCA\Tables\Errors\PermissionError;
use OCA\Tables\Middleware\Attribute\RequireTable;
use OCA\Tables\ResponseDefinitions;
use OCA\Tables\Service\TableService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IL10N;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

/**
 * @psalm-import-type TablesTable from ResponseDefinitions
 */
class ApiTablesController extends AEnvironmentAwareOCSController {
	private TableService $service;

	public function __construct(
		IRequest $request,
		LoggerInterface $logger,
		TableService $service,
		IL10N $n,
		string $userId) {
		parent::__construct($request, $logger, $n, $userId);
		$this->service = $service;
	}

	/**
	 * [api v2] Returns all Tables
	 *
	 * @return DataResponse<Http::STATUS_OK, TablesTable[], array{}>|DataResponse<Http::STATUS_INTERNAL_SERVER_ERROR, array{message: string}, array{}>
	 *
	 * 200: Tables returned
	 */
	#[NoAdminRequired]
	public function index(): DataResponse {
		try {
			return new DataResponse($this->service->formatTables($this->service->findAll($this->userId)));
		} catch (InternalError|Exception $e) {
			return $this->handleError($e);
		}
	}

	/**
	 * [api v2] Get a table object
	 *
	 * @return DataResponse<Http::STATUS_OK, TablesTable, array{}>|DataResponse<Http::STATUS_FORBIDDEN|Http::STATUS_INTERNAL_SERVER_ERROR|Http::STATUS_NOT_FOUND, array{message: string}, array{}>
	 *
	 * 200: Table returned
	 * 403: No permissions
	 * 404: Not found
	 */
	#[NoAdminRequired]
	#[RequireTable(enhance: true)]
	public function show(): DataResponse {
		return new DataResponse($this->getTable()->jsonSerialize());
	}

	/**
	 * [api v2] Create a new table and return it
	 *
	 * @param string $title Title of the table
	 * @param string|null $emoji Emoji for the table
	 * @param string $template Template to use if wanted
	 *
	 * @return DataResponse<Http::STATUS_OK, TablesTable, array{}>|DataResponse<Http::STATUS_INTERNAL_SERVER_ERROR, array{message: string}, array{}>
	 *
	 * 200: Tables returned
	 */
	#[NoAdminRequired]
	public function create(string $title, ?string $emoji, string $template = 'custom'): DataResponse {
		try {
			return new DataResponse($this->service->create($title, $template, $emoji)->jsonSerialize());
		} catch (InternalError|Exception $e) {
			return $this->handleError($e);
		}
	}

	/**
	 * [api v2] Update tables properties
	 *
	 * @param string|null $title New table title
	 * @param string|null $emoji New table emoji
	 * @param bool $archived whether the table is archived
	 * @return DataResponse<Http::STATUS_OK, TablesTable, array{}>|DataResponse<Http::STATUS_FORBIDDEN|Http::STATUS_INTERNAL_SERVER_ERROR|Http::STATUS_NOT_FOUND, array{message: string}, array{}>
	 *
	 * 200: Tables returned
	 * 403: No permissions
	 * 404: Not found
	 *
	 * @throws InternalError
	 * @throws NotFoundError
	 * @throws PermissionError
	 */
	#[NoAdminRequired]
	#[RequireTable(enhance: true)]
	public function update(?string $title = null, ?string $emoji = null, ?bool $archived = null): DataResponse {
		// TODO: service class to accept Table instead of ID
		return new DataResponse($this->service->update($this->getTable()->getId(), $title, $emoji, $archived, $this->userId)->jsonSerialize());
	}

	/**
	 * [api v2] Delete a table
	 *
	 * @return DataResponse<Http::STATUS_OK, TablesTable, array{}>|DataResponse<Http::STATUS_FORBIDDEN|Http::STATUS_INTERNAL_SERVER_ERROR|Http::STATUS_NOT_FOUND, array{message: string}, array{}>
	 *
	 * 200: Deleted table returned
	 * 403: No permissions
	 * 404: Not found
	 *
	 * @throws InternalError
	 * @throws NotFoundError
	 * @throws PermissionError
	 */
	#[NoAdminRequired]
	#[RequireTable(enhance: true)]
	public function destroy(): DataResponse {
		// TODO: service class to accept Table instead of ID
		return new DataResponse($this->service->delete($this->getTable()->getId())->jsonSerialize());
	}

	/**
	 * [api v2] Transfer table
	 *
	 * Transfer table from one user to another
	 *
	 * @NoAdminRequired
	 *
	 * @param string $newOwnerUserId New user ID
	 *
	 * @return DataResponse<Http::STATUS_OK, TablesTable, array{}>|DataResponse<Http::STATUS_FORBIDDEN|Http::STATUS_INTERNAL_SERVER_ERROR|Http::STATUS_NOT_FOUND, array{message: string}, array{}>
	 *
	 * 200: Ownership changed
	 * 403: No permissions
	 * 404: Not found
	 *
	 * @throws InternalError
	 * @throws NotFoundError
	 * @throws PermissionError
	 */
	#[NoAdminRequired]
	#[RequireTable(enhance: true)]
	public function transfer(string $newOwnerUserId): DataResponse {
		// TODO: service class to accept Table instead of ID
		return new DataResponse($this->service->setOwner($this->getTable()->getId(), $newOwnerUserId)->jsonSerialize());
	}
}
