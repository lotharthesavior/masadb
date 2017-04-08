<?php

namespace Models\Traits;

trait GitWorkflow
{

	/**
	 * 
	 */
	public function saveVersion(){

		$result_stage = $this->stageChanges();

		$result_commit = $this->commitChanges();

		return $result_stage && $result_commit;

	}

	/**
	 * @todo analyze the result
	 */
	private function stageChanges(){

		$this->repo->add();

		return true;

	}

	/**
	 * @todo analyze the result
	 */
	private function commitChanges(){

		$message = "Commit from Masa manager - " . date("Y-d-m H:i:s") . ". - by Savio";

		$this->repo->commit( $message );

		return true;

	}

}