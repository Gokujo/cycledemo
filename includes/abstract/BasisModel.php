<?php

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\ORM\Entity\Behavior;


#[Behavior\CreatedAt(
	column: 'created_at'
)]
#[Behavior\UpdatedAt(
	column: 'updated_at'
)]
class BasisModel {
	#[Column(type: 'bigPrimary', primary: true, autoincrement: true)]
	protected int $id;

	#[Column(type: 'datetime')]
	protected \DateTimeImmutable $createdAt;

	#[Column(type: 'datetime', nullable: true)]
	protected ?\DateTimeImmutable $updatedAt = null;

	public function getId() : int {
		return $this->id;
	}

	public function getCreatedAt() : DateTimeImmutable {
		return $this->createdAt;
	}

	public function getUpdatedAt() : ?DateTimeImmutable {
		return $this->updatedAt;
	}



}