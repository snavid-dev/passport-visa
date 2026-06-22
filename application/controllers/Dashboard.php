<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard — landing page after login.
 *
 * Phase 1: renders the shell with placeholder KPI cards so the design
 * system + layout are verifiable. Live metrics are wired in later phases.
 */
class Dashboard extends MY_Controller {

    public function index()
    {
        $data = array(
            'page_title' => 'داشبورد',
        );
        $this->render('dashboard/index', $data);
    }
}
