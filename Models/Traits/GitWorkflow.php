<?php

namespace Models\Traits;

trait GitWorkflow
{

	/**
	 * 
	 */
	public function saveVersion(){

		$result_stage = $this->git->stageChanges();

		$result_commit = $this->git->commitChanges();

		return $result_stage && $result_commit;

	}

}