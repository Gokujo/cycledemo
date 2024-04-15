<?php

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity(role : 'mhlog', repository : MhLogRepository::class, table : 'maharder_logs')]
//#[\Cycle\Annotated\Annotation\Inheritance\SingleTable]
class MhLog extends BasisModel {
	#[Column(type : 'string')]
	private string            $type;
	#[Column(type : 'string')]
	private string            $plugin;
	#[Column(type : 'string')]
	private string            $fn_name;
	#[Column(type : 'datetime')]
	private DateTimeImmutable $time;
	#[Column(type : 'text')]
	private string            $message;

	public function getType() : string {
		return $this->type;
	}

	public function setType(string $type) : void {
		$this->type = $type;
	}

	public function getPlugin() : string {
		return $this->plugin;
	}

	public function setPlugin(string $plugin) : void {
		$this->plugin = $plugin;
	}

	public function getFnName() : string {
		return $this->fn_name;
	}

	public function setFnName(string $fn_name) : void {
		$this->fn_name = $fn_name;
	}

	public function getTime() : DateTimeImmutable {
		return $this->time;
	}

	public function setTime(DateTimeImmutable $time) : void {
		$this->time = $time;
	}

	public function getMessage() : string {
		return $this->message;
	}

	public function setMessage(string $message) : void {
		$this->message = $message;
	}


}