<?php

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Tables\Helper;

use OCA\Circles\CirclesManager;
use OCA\Circles\Model\Member;
use OCA\Circles\Model\Probes\CircleProbe;
use OCP\App\IAppManager;
use OCP\Server;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @psalm-suppress UndefinedClass
 */
class CircleHelper {
	private LoggerInterface $logger;
	private bool $circlesEnabled;
	private ?CirclesManager $circlesManager;

	/**
	 * @psalm-suppress UndefinedClass
	 */
	public function __construct(
		LoggerInterface $logger,
		IAppManager     $appManager,
		?CirclesManager $circlesManager = null
	) {
		$this->logger = $logger;
		$this->circlesEnabled = $appManager->isEnabledForUser('circles');
		if ($this->circlesEnabled) {
			try {
				$this->circlesManager = $circlesManager ?? Server::get(CirclesManager::class);
			} catch (Throwable $e) {
				$this->logger->warning('Failed to get CirclesManager: ' . $e->getMessage());
				$this->circlesManager = null;
				$this->circlesEnabled = false;
			}
		} else {
			$this->circlesManager = null;
		}
	}

	public function isCirclesEnabled(): bool {
		return $this->circlesEnabled;
	}

	public function getCircleDisplayName(string $circleId, string $userId): string {
		if (!$this->circlesEnabled) {
			return $circleId;
		}

		try {
			$federatedUser = $this->circlesManager->getFederatedUser($userId, Member::TYPE_USER);
			$this->circlesManager->startSession($federatedUser);

			$circle = $this->circlesManager->getCircle($circleId);
			return $circle ? ($circle->getDisplayName() ?: $circleId) : $circleId;
		} catch (Throwable $e) {
			$this->logger->warning('Failed to get circle display name: ' . $e->getMessage(), [
				'circleId' => $circleId,
				'userId' => $userId
			]);
			return $circleId;
		}
	}

	public function getUserCircles(string $userId): array {
		if (!$this->circlesEnabled) {
			return [];
		}

		try {
			$federatedUser = $this->circlesManager->getFederatedUser($userId, Member::TYPE_USER);
			$this->circlesManager->startSession($federatedUser);
			$probe = new CircleProbe();
			$probe->mustBeMember();
			return $this->circlesManager->getCircles($probe);
		} catch (Throwable $e) {
			$this->logger->warning('Failed to get user circles: ' . $e->getMessage());
			return [];
		}
	}
}
