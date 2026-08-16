@extends('admin.template')

@section('title-sub', 'Manual Booking')
@section('pagetitle', 'Manual Booking')

@section('css')
<style type="text/css">
	.manual-booking-page {
		--dispatch-primary: var(--color-as-0d6efd);
		--dispatch-dark: var(--color-as-212529);
		--dispatch-muted: var(--color-as-6c757d);
		--dispatch-border: var(--color-as-dee2e6);
		--dispatch-success: var(--color-as-198754);
		--dispatch-danger: var(--color-as-dc3545);
		--dispatch-warning: var(--color-as-ffc107);
		--dispatch-info: var(--color-as-0d6efd);
		--dispatch-surface: var(--color-white);
		--dispatch-soft: var(--color-as-f8f9fa);
		--dispatch-shadow: 0 6px 18px var(--color-as-rgba-0-0-0-0-08);
	}

	.manual-booking-page .dispatch-console-shell {
		display: flex;
		flex-direction: column;
		gap: 1rem;
	}

	.manual-booking-page .dispatch-console-header {
		background: var(--color-white);
		border: 1px solid var(--dispatch-border);
		border-radius: 0;
		padding: 1.25rem 1.35rem;
		color: var(--dispatch-dark);
		box-shadow: var(--dispatch-shadow);
	}

	.manual-booking-page .dispatch-header-top {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 1rem;
	}

	.manual-booking-page .dispatch-header-title {
		margin: .35rem 0 0;
		font-size: 1.9rem;
		font-weight: 800;
		letter-spacing: -.03em;
	}

	.manual-booking-page .dispatch-header-copy {
		margin: .5rem 0 0;
		max-width: 60rem;
		color: var(--dispatch-muted);
		line-height: 1.65;
	}

	.manual-booking-page .dispatch-header-badge {
		display: inline-flex;
		align-items: center;
		gap: .45rem;
		padding: .45rem .8rem;
		border-radius: 0;
		background: var(--color-as-e7f1ff);
		color: var(--color-as-0d6efd);
		font-size: .78rem;
		font-weight: 700;
		letter-spacing: .06em;
		text-transform: uppercase;
	}

	.manual-booking-page .dispatch-header-metrics {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: .85rem;
		margin-top: 1.15rem;
	}

	.manual-booking-page .dispatch-metric {
		background: var(--color-as-f8f9fa);
		border: 1px solid var(--dispatch-border);
		border-radius: 0;
		padding: .95rem 1rem;
	}

	.manual-booking-page .dispatch-metric-label {
		display: block;
		color: var(--dispatch-muted);
		font-size: .74rem;
		font-weight: 700;
		letter-spacing: .06em;
		text-transform: uppercase;
	}

	.manual-booking-page .dispatch-metric-value {
		margin-top: .35rem;
		font-size: 1rem;
		font-weight: 800;
	}

	.manual-booking-page .dispatch-main-grid {
		display: grid;
		grid-template-columns: minmax(360px, 30%) minmax(520px, 1fr) minmax(320px, 26%);
		gap: .9rem;
		align-items: start;
	}

	.manual-booking-page .dispatch-booking-panel,
	.manual-booking-page .dispatch-map-panel,
	.manual-booking-page .dispatch-side-panel,
	.manual-booking-page .dispatch-queue-panel {
		min-width: 0;
	}

	.manual-booking-page .dispatch-panel-card {
		background: var(--dispatch-surface);
		border: 1px solid var(--color-as-rgba-148-163-184-0-18);
		border-radius: 0;
		box-shadow: var(--dispatch-shadow);
		overflow: hidden;
		height: 100%;
	}

	.manual-booking-page .dispatch-panel-head {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: .75rem;
		padding: 1rem 1rem 0;
	}

	.manual-booking-page .dispatch-panel-title {
		margin: 0;
		color: var(--dispatch-dark);
		font-size: 1rem;
		font-weight: 800;
	}

	.manual-booking-page .dispatch-panel-subtitle {
		margin: .2rem 0 0;
		color: var(--dispatch-muted);
		font-size: .86rem;
		line-height: 1.55;
	}

	.manual-booking-page .dispatch-panel-body {
		padding: 1rem;
	}

	.manual-booking-page .dispatch-block {
		background: var(--dispatch-soft);
		border: 1px solid var(--color-as-e2e8f0);
		border-radius: 0;
		padding: .95rem;
		margin-bottom: .9rem;
	}

	.manual-booking-page .dispatch-block:last-child {
		margin-bottom: 0;
	}

	.manual-booking-page .dispatch-block-title {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: .75rem;
		margin-bottom: .75rem;
	}

	.manual-booking-page .dispatch-block-title h6 {
		margin: 0;
		font-size: .92rem;
		font-weight: 800;
		color: var(--dispatch-dark);
	}

	.manual-booking-page .dispatch-inline-hint {
		color: var(--dispatch-muted);
		font-size: .75rem;
		font-weight: 700;
	}

	.manual-booking-page .form-label {
		margin-bottom: .35rem;
		font-size: .82rem;
		font-weight: 700;
		color: var(--dispatch-dark);
	}

	.manual-booking-page .form-control,
	.manual-booking-page .form-select,
	.manual-booking-page .change_field,
	.manual-booking-page .bootstrap-select > .dropdown-toggle {
		min-height: 40px;
		border-radius: 0 !important;
		border-color: var(--color-as-ced7e2);
		box-shadow: none;
		font-size: .92rem;
	}

	.manual-booking-page .form-control:focus,
	.manual-booking-page .form-select:focus,
	.manual-booking-page .change_field:focus {
		border-color: var(--color-as-rgba-37-99-235-0-45);
		box-shadow: 0 0 0 .18rem var(--color-as-rgba-37-99-235-0-12);
	}

	.manual-booking-page textarea.form-control {
		min-height: 78px;
		resize: vertical;
	}

	.manual-booking-page .dispatch-mode-pills {
		display: flex;
		flex-wrap: wrap;
		gap: .55rem;
	}

	.manual-booking-page .dispatch-mode-pill,
	.manual-booking-page .dispatch-status-pill,
	.manual-booking-page .dispatch-shortcut {
		display: inline-flex;
		align-items: center;
		gap: .35rem;
		padding: .42rem .7rem;
		border-radius: 0;
		font-size: .74rem;
		font-weight: 800;
		letter-spacing: .03em;
	}

	.manual-booking-page .dispatch-mode-pill {
		background: var(--color-as-e2e8f0);
		color: var(--color-as-334155);
	}

	.manual-booking-page .dispatch-mode-pill.active {
		background: var(--color-as-e7f1ff);
		color: var(--color-as-0d6efd);
	}

	.manual-booking-page .dispatch-status-pill {
		background: var(--color-as-rgba-37-99-235-0-1);
		color: var(--color-as-1d4ed8);
	}

	.manual-booking-page .dispatch-status-pill.warning {
		background: var(--color-as-rgba-245-158-11-0-13);
		color: var(--color-as-b45309);
	}

	.manual-booking-page .dispatch-status-pill.danger {
		background: var(--color-as-rgba-220-38-38-0-12);
		color: var(--color-as-b91c1c);
	}

	.manual-booking-page .dispatch-status-pill.success {
		background: var(--color-as-rgba-22-163-74-0-12);
		color: var(--color-as-15803d);
	}

	.manual-booking-page .dispatch-shortcut {
		background: var(--color-as-e9ecef);
		color: var(--color-as-495057);
		font-size: .68rem;
	}

	.manual-booking-page .dispatch-customer-status {
		display: flex;
		flex-wrap: wrap;
		gap: .55rem;
	}

	.manual-booking-page .dispatch-split-phone {
		display: grid;
		grid-template-columns: 104px minmax(0, 1fr);
		gap: .5rem;
	}

	.manual-booking-page .dispatch-form-grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: .75rem;
	}

	.manual-booking-page .dispatch-form-grid.single {
		grid-template-columns: 1fr;
	}

	.manual-booking-page .dispatch-preferences {
		padding: .7rem .8rem;
		border: 1px dashed var(--color-as-cbd5e1);
		border-radius: 0;
		background: var(--color-white);
		color: var(--dispatch-muted);
		font-size: .83rem;
	}

	.manual-booking-page .soft-panel {
		background: var(--color-white);
		border: 1px solid var(--color-as-dbe4ee);
		border-radius: 0;
		padding: .85rem;
	}

	.manual-booking-page .dispatch-actions-inline {
		display: flex;
		align-items: center;
		gap: .5rem;
		flex-wrap: wrap;
	}

	.manual-booking-page .dispatch-ghost-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: .35rem;
		min-height: 40px;
		padding: 0 .9rem;
		border-radius: 0;
		border: 1px solid var(--color-as-dbe4ee);
		background: var(--color-white);
		color: var(--color-as-334155);
		font-size: .82rem;
		font-weight: 700;
	}

	.manual-booking-page .dispatch-ghost-btn:hover {
		background: var(--color-as-f8fafc);
		color: var(--color-as-0f172a);
	}

	.manual-booking-page .dispatch-checkbox-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: .75rem;
	}

	.manual-booking-page .dispatch-assign-state {
		display: none;
	}

	.manual-booking-page .assigned_driver .col-md-12 {
		border-radius: 0;
		padding: .9rem 1rem;
	}

	.manual-booking-page .dispatch-driver-search {
		position: relative;
	}

	.manual-booking-page .dispatch-driver-search .dispatch-shortcut {
		position: absolute;
		right: .6rem;
		top: 50%;
		transform: translateY(-50%);
	}

	.manual-booking-page .map-route-option {
		display: flex;
		flex-direction: column;
		gap: .9rem;
	}

	.manual-booking-page .dispatch-map-topbar {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: .65rem;
		margin-bottom: .8rem;
	}

	.manual-booking-page .dispatch-map-stat {
		background: var(--dispatch-soft);
		border: 1px solid var(--color-as-e2e8f0);
		border-radius: 0;
		padding: .75rem .8rem;
	}

	.manual-booking-page .dispatch-map-stat-label {
		display: block;
		color: var(--dispatch-muted);
		font-size: .72rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: .05em;
	}

	.manual-booking-page .dispatch-map-stat-value {
		display: block;
		margin-top: .28rem;
		color: var(--dispatch-dark);
		font-size: .95rem;
		font-weight: 800;
	}

	.manual-booking-page .dispatch-map-toolbar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: .75rem;
		flex-wrap: wrap;
		padding: .95rem;
		background: var(--color-white);
		border: 1px solid var(--color-as-e2e8f0);
		border-radius: 0;
	}

	.manual-booking-page .dispatch-map-tabs {
		display: flex;
		flex-wrap: wrap;
		gap: .5rem;
	}

	.manual-booking-page .dispatch-map-tab {
		border: 0;
		padding: .45rem .75rem;
		border-radius: 0;
		background: var(--color-as-eef2f7);
		color: var(--color-as-334155);
		font-size: .78rem;
		font-weight: 800;
	}

	.manual-booking-page .dispatch-map-tab.active {
		background: var(--color-as-e7f1ff);
		color: var(--color-as-0d6efd);
	}

	.manual-booking-page .dispatch-map-controls {
		display: flex;
		flex-wrap: wrap;
		gap: .45rem;
	}

	.manual-booking-page .dispatch-map-filter-grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: .75rem;
	}

	.manual-booking-page .map-view {
		position: relative;
		background: var(--color-white);
		border: 1px solid var(--color-as-e2e8f0);
		border-radius: 0;
		padding: .75rem;
	}

	.manual-booking-page #map {
		width: 100%;
		height: 560px;
		border-radius: 0;
		background: var(--color-as-f8fafc);
	}

	.manual-booking-page .dispatch-map-overlay {
		position: absolute;
		right: 1.35rem;
		top: 1.35rem;
		z-index: 20;
		display: flex;
		flex-direction: column;
		gap: .45rem;
	}

	.manual-booking-page .dispatch-map-overlay .dispatch-ghost-btn {
		box-shadow: 0 10px 20px var(--color-as-rgba-15-23-42-0-08);
	}

	.manual-booking-page .driver_detail_popup {
		border: 1px solid var(--color-as-e2e8f0);
		border-radius: 0;
		padding: 1rem;
		box-shadow: 0 18px 50px var(--color-as-rgba-15-23-42-0-08);
	}

	.manual-booking-page .dispatch-checklist {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: .55rem;
	}

	.manual-booking-page .dispatch-check-item {
		display: flex;
		align-items: center;
		gap: .55rem;
		padding: .7rem .8rem;
		border: 1px solid var(--color-as-e2e8f0);
		border-radius: 0;
		background: var(--color-white);
		font-size: .82rem;
		font-weight: 700;
		color: var(--color-as-334155);
	}

	.manual-booking-page .dispatch-check-dot {
		width: 10px;
		height: 10px;
		border-radius: 0;
		background: var(--color-as-cbd5e1);
		flex-shrink: 0;
	}

	.manual-booking-page .dispatch-check-item.complete .dispatch-check-dot {
		background: var(--dispatch-success);
	}

	.manual-booking-page .dispatch-check-item.pending .dispatch-check-dot {
		background: var(--dispatch-warning);
	}

	.manual-booking-page .dispatch-driver-recommended {
		padding: .95rem;
		border-radius: 0;
		background: var(--color-as-f8f9fa);
		border: 1px solid var(--dispatch-border);
	}

	.manual-booking-page .dispatch-driver-card {
		padding: 0;
		border-radius: 0;
		border: 1px solid var(--color-as-e2e8f0);
		background: var(--color-white);
		box-shadow: 0 10px 24px var(--color-as-rgba-15-23-42-0-04);
		margin-bottom: .75rem;
	}

	.manual-booking-page .driver_detail_view {
		display: grid;
		grid-template-columns: 64px minmax(0, 1fr) auto;
		gap: .75rem;
		align-items: center;
		width: 100%;
		padding: .9rem;
	}

	.manual-booking-page .driver_detail_view > a {
		display: contents;
		color: inherit !important;
		text-decoration: none;
	}

	.manual-booking-page .driver_detail_view > a .col-md-3,
	.manual-booking-page .driver_detail_view > a .col-md-5,
	.manual-booking-page .driver_detail_view > .dispatch-driver-actions {
		float: none;
		width: auto;
		padding: 0;
	}

	.manual-booking-page .dispatch-booking-panel .dispatch-panel-card {
		position: sticky;
		top: 1rem;
	}

	.manual-booking-page .dispatch-booking-panel .dispatch-panel-body {
		max-height: calc(100vh - 12.5rem);
		overflow: auto;
	}

	.manual-booking-page .dispatch-side-panel {
		display: grid;
		gap: .9rem;
	}

	.manual-booking-page .dispatch-side-panel .dispatch-panel-card {
		margin-bottom: 0 !important;
	}

	.manual-booking-page .dispatch-driver-card:last-child {
		margin-bottom: 0;
	}

	.manual-booking-page .dispatch-driver-avatar {
		width: 64px;
		height: 64px;
		border-radius: 0;
		object-fit: cover;
		background: var(--color-as-e2e8f0);
	}

	.manual-booking-page .dispatch-driver-name {
		margin: 0;
		color: var(--dispatch-dark);
		font-size: .96rem;
		font-weight: 800;
	}

	.manual-booking-page .dispatch-driver-meta {
		margin: .18rem 0 0;
		color: var(--dispatch-muted);
		font-size: .8rem;
		line-height: 1.55;
	}

	.manual-booking-page .dispatch-driver-actions {
		display: flex;
		flex-direction: column;
		align-items: flex-end;
		gap: .45rem;
	}

	.manual-booking-page .dispatch-driver-actions .btn {
		min-width: 112px;
		border-radius: 0;
	}

	.manual-booking-page .dispatch-driver-status {
		display: inline-flex;
		align-items: center;
		gap: .35rem;
		padding: .32rem .55rem;
		border-radius: 0;
		font-size: .72rem;
		font-weight: 800;
	}

	.manual-booking-page .dispatch-driver-status.available,
	.manual-booking-page .dispatch-driver-status.assigned,
	.manual-booking-page .dispatch-driver-status.nearby {
		background: var(--color-as-rgba-22-163-74-0-12);
		color: var(--color-as-15803d);
	}

	.manual-booking-page .dispatch-driver-status.busy {
		background: var(--color-as-rgba-245-158-11-0-14);
		color: var(--color-as-b45309);
	}

	.manual-booking-page .dispatch-driver-status.offline {
		background: var(--color-as-rgba-220-38-38-0-12);
		color: var(--color-as-b91c1c);
	}

	.manual-booking-page .fare-table {
		background: var(--color-white);
		border: 1px solid var(--color-as-e2e8f0);
		border-radius: 0;
		padding: 1rem 1.05rem;
	}

	.manual-booking-page .fare-table h3 {
		margin: 0 0 .35rem;
		color: var(--dispatch-dark);
		font-size: 1rem;
		font-weight: 800;
	}

	.manual-booking-page .fare-subcopy {
		margin: 0 0 .85rem;
		color: var(--dispatch-muted);
		font-size: .8rem;
		line-height: 1.55;
	}

	.manual-booking-page .fare-table .row {
		padding: .45rem 0;
		border-bottom: 1px dashed var(--color-as-e2e8f0);
	}

	.manual-booking-page .fare-table .row:last-child {
		border-bottom: 0;
	}

	.manual-booking-page .dispatch-peak-pill {
		display: inline-flex;
		align-items: center;
		gap: .3rem;
		margin-bottom: .75rem;
		padding: .35rem .6rem;
		border-radius: 0;
		background: var(--color-as-fff3cd);
		color: var(--color-as-856404);
		font-size: .72rem;
		font-weight: 800;
	}

	.manual-booking-page .dispatch-total-fare {
		margin-top: .8rem;
		padding: .95rem 1rem;
		border-radius: 0;
		background: var(--color-as-f8f9fa);
		border: 1px solid var(--dispatch-border);
	}

	.manual-booking-page .dispatch-total-fare small {
		display: block;
		color: var(--dispatch-muted);
		font-size: .72rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: .06em;
	}

	.manual-booking-page .dispatch-total-fare strong {
		display: block;
		margin-top: .2rem;
		color: var(--dispatch-dark);
		font-size: 1.8rem;
		font-weight: 900;
	}

	.manual-booking-page .dispatch-validation-area {
		padding: .75rem .85rem;
		border-radius: 0;
		border: 1px dashed var(--color-as-f5c2c7);
		background: var(--color-as-f8d7da);
		color: var(--color-as-842029);
		font-size: .8rem;
		line-height: 1.6;
	}

	.manual-booking-page .dispatch-sticky-actions {
		position: sticky;
		bottom: .75rem;
		z-index: 30;
		padding-top: .75rem;
	}

	.manual-booking-page .dispatch-sticky-bar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: .8rem;
		padding: .9rem 1rem;
		border-radius: 0;
		background: var(--color-white);
		color: var(--dispatch-dark);
		border: 1px solid var(--dispatch-border);
		box-shadow: var(--dispatch-shadow);
	}

	.manual-booking-page .dispatch-sticky-summary {
		display: flex;
		flex-wrap: wrap;
		gap: 1rem;
		align-items: center;
	}

	.manual-booking-page .dispatch-sticky-summary-item {
		display: flex;
		flex-direction: column;
		gap: .1rem;
	}

	.manual-booking-page .dispatch-sticky-summary-item small {
		color: var(--dispatch-muted);
		font-size: .68rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: .05em;
	}

	.manual-booking-page .dispatch-sticky-summary-item strong {
		font-size: .96rem;
		font-weight: 800;
	}

	.manual-booking-page .fare-btn {
		display: none;
	}

	.manual-booking-page .dispatch-sticky-bar .btn {
		min-width: 136px;
		border-radius: 0;
	}

	.manual-booking-page .dispatch-sticky-bar .btn-primary {
		background: var(--dispatch-primary);
		border-color: var(--dispatch-primary);
	}

	.manual-booking-page .dispatch-sticky-bar .btn-outline-light {
		border-color: var(--color-as-ced4da);
		color: var(--color-as-495057);
	}

	.manual-booking-page .dispatch-queue-panel .nav {
		gap: .5rem;
		flex-wrap: nowrap;
		overflow-x: auto;
		padding-bottom: .2rem;
	}

	.manual-booking-page .dispatch-queue-panel .nav-link {
		border: 1px solid var(--color-as-dbe4ee);
		border-radius: 0;
		color: var(--color-as-334155);
		font-size: .78rem;
		font-weight: 800;
		padding: .45rem .8rem;
		white-space: nowrap;
		background: var(--color-white);
	}

	.manual-booking-page .dispatch-queue-panel .nav-link.active {
		background: var(--color-as-e7f1ff);
		border-color: var(--color-as-b6d4fe);
		color: var(--color-as-0d6efd);
	}

	.manual-booking-page .dispatch-map-panel .dispatch-panel-card,
	.manual-booking-page .dispatch-queue-panel .dispatch-panel-card {
		min-height: 100%;
	}

	.manual-booking-page .dispatch-queue-empty {
		padding: 1.1rem;
		border: 1px dashed var(--color-as-cbd5e1);
		border-radius: 0;
		background: var(--color-as-f8fafc);
		color: var(--dispatch-muted);
		text-align: center;
		line-height: 1.7;
	}

	.manual-booking-page .dispatch-operational-toggle {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: .75rem;
		width: 100%;
		padding: .85rem 0 0;
		background: transparent;
		border: 0;
		color: var(--dispatch-dark);
		font-weight: 800;
		text-align: left;
	}

	.manual-booking-page .manual-notes {
		padding-top: .85rem;
	}

	.manual-booking-page .manual-notes h4,
	.manual-booking-page .manual-notes h5 {
		margin-top: 0;
		color: var(--dispatch-dark);
		font-weight: 800;
	}

	.manual-booking-page .manual-notes ul {
		padding-left: 1.1rem;
		margin-bottom: .9rem;
	}

	.manual-booking-page .manual-notes li {
		color: var(--color-as-475569);
		margin-bottom: .42rem;
		line-height: 1.62;
	}

	.manual-booking-page .error_msg {
		display: block;
		margin-top: .25rem;
		font-size: .76rem;
	}

	.manual-booking-page .dispatch-form-alert {
		border-radius: 0;
	}

	.loader {
		border: 2px solid var(--color-as-f3f3f3);
		border-radius: 0;
		border-top: 2px solid var(--color-as-0d6efd);
		border-bottom: 2px solid var(--color-as-0d6efd);
		width: 20px;
		height: 20px;
		-webkit-animation: spin 2s linear infinite;
		animation: spin 1s linear infinite;
	}

	@-webkit-keyframes spin {
		0% { -webkit-transform: rotate(0deg); }
		100% { -webkit-transform: rotate(360deg); }
	}

	@keyframes spin {
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}

	@media (max-width: 1399px) {
		.manual-booking-page .dispatch-main-grid {
			grid-template-columns: 36% 39% 25%;
		}
	}

	@media (max-width: 1199px) {
		.manual-booking-page .dispatch-main-grid {
			grid-template-columns: 1fr;
		}

		.manual-booking-page .dispatch-booking-panel .dispatch-panel-card {
			position: static;
		}

		.manual-booking-page .dispatch-booking-panel .dispatch-panel-body {
			max-height: none;
			overflow: visible;
		}

		.manual-booking-page .dispatch-header-metrics,
		.manual-booking-page .dispatch-map-topbar,
		.manual-booking-page .dispatch-map-filter-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}

		.manual-booking-page .dispatch-sticky-bar {
			flex-direction: column;
			align-items: stretch;
		}

		.manual-booking-page .dispatch-sticky-summary {
			justify-content: space-between;
		}
	}

	@media (max-width: 767px) {
		.manual-booking-page .dispatch-header-top,
		.manual-booking-page .dispatch-map-toolbar,
		.manual-booking-page .dispatch-sticky-summary,
		.manual-booking-page .dispatch-actions-inline {
			flex-direction: column;
			align-items: stretch;
		}

		.manual-booking-page .dispatch-header-metrics,
		.manual-booking-page .dispatch-map-topbar,
		.manual-booking-page .dispatch-map-filter-grid,
		.manual-booking-page .dispatch-checklist,
		.manual-booking-page .dispatch-form-grid,
		.manual-booking-page .dispatch-split-phone {
			grid-template-columns: 1fr;
		}

		.manual-booking-page .dispatch-driver-card {
			grid-template-columns: 1fr;
		}

		.manual-booking-page .dispatch-driver-actions {
			align-items: stretch;
		}

		.manual-booking-page .dispatch-driver-actions .btn,
		.manual-booking-page .dispatch-sticky-bar .btn,
		.manual-booking-page .dispatch-ghost-btn {
			width: 100%;
		}

		.manual-booking-page #map {
			height: 380px;
		}
	}
</style>
@endsection

@section('main')
<div id="layout-wrapper" class="manual-booking-page">
	@php
		$ride = optional($schedule_ride);
		$rideUser = optional($ride->users);
		$rideCountryCode = $rideUser->country_code ?? $country_code_option[0]->phone_code;
	@endphp
	<div class="dispatch-console-shell" ng-controller='manual_booking' ng-init='vehicle_types = @json($vehicle_types)'>
		<div class="dispatch-console-header">
			<div class="dispatch-header-top">
				<div>
					<span class="dispatch-header-badge">
						<i class="bi bi-broadcast-pin"></i>
						Dispatch Command Center
					</span>
					<h2 class="dispatch-header-title">Manual Taxi Dispatch</h2>
					<p class="dispatch-header-copy">A high-speed operator console for creating, scheduling, assigning, and monitoring bookings without disrupting the current Laravel and AngularJS workflow.</p>
				</div>
				<div class="dispatch-actions-inline">
					<span class="dispatch-shortcut">F2 Pickup</span>
					<span class="dispatch-shortcut">F3 Drop</span>
					<span class="dispatch-shortcut">F4 Name</span>
					<span class="dispatch-shortcut">F6 Search</span>
					<span class="dispatch-shortcut">F9 Save</span>
				</div>
			</div>
			<div class="dispatch-header-metrics">
				<div class="dispatch-metric">
					<span class="dispatch-metric-label">Mode</span>
					<span class="dispatch-metric-value">{{ isset($schedule_ride->id) ? 'Edit Booking' : 'New Dispatch' }}</span>
				</div>
				<div class="dispatch-metric">
					<span class="dispatch-metric-label">Assignment</span>
					<span class="dispatch-metric-value">Manual or Auto Assign</span>
				</div>
				<div class="dispatch-metric">
					<span class="dispatch-metric-label">Map Intelligence</span>
					<span class="dispatch-metric-value">Route + Driver Scan</span>
				</div>
				<div class="dispatch-metric">
					<span class="dispatch-metric-label">Fare Engine</span>
					<span class="dispatch-metric-value">Live Estimate</span>
				</div>
			</div>
		</div>

		{!! Form::open(['method'=>'POST','url' => LOGIN_USER_TYPE.'/manual_booking/store', 'class' => 'manual_booking','id'=>'manual_booking','name'=>'myForm']) !!}
		{!! Form::hidden('manual_booking_id', $ride->id, ['id' => 'manual_booking_id']) !!}
		{!! Form::hidden('auto_assign_id', $ride->driver_id, ['id' => 'auto_assign_id']) !!}
		{!! Form::hidden('utc_offset', '', ['id' => 'utc_offset']) !!}
		{!! Form::hidden('location_id', $ride->location_id, ['id' => 'location_id']) !!}
		{!! Form::hidden('peak_id', $ride->peak_id, ['id' => 'peak_id']) !!}
		{!! Form::hidden('user_id','',['id'=>'user_id']) !!}
		{!! Form::hidden('booking_mode', $ride->booking_mode ?: 'asap', ['id' => 'booking_mode']) !!}
		{!! Form::hidden('dispatch_mode', $ride->dispatch_mode ?: (($ride->driver_id ?? 0) ? 'manual' : 'auto'), ['id' => 'dispatch_mode']) !!}
		{!! Form::hidden('assign_now', old('assign_now', $ride->assign_now ? 1 : 0), ['id' => 'assign_now']) !!}
		{!! Form::hidden('is_dispatch_override', old('is_dispatch_override', $ride->is_dispatch_override ? 1 : 0), ['id' => 'is_dispatch_override']) !!}

		<div class="alert alert-danger dispatch-form-alert d-none" id="dispatch-form-alert">
			<strong>Form attention needed.</strong> Please review the highlighted booking fields before submitting.
		</div>

		<div class="dispatch-main-grid">
			<div class="dispatch-booking-panel">
				<div class="dispatch-panel-card">
					<div class="dispatch-panel-head">
						<div>
							<h5 class="dispatch-panel-title">Booking Entry</h5>
							<p class="dispatch-panel-subtitle">Enter passenger details, trip information, and assignment preferences with operator-first speed.</p>
						</div>
						<div class="dispatch-mode-pills">
							<span class="dispatch-mode-pill {{ !isset($schedule_ride->id) ? 'active' : '' }}">Book Now</span>
							<span class="dispatch-mode-pill {{ isset($schedule_ride->id) ? 'active' : '' }}">Schedule</span>
							<span class="dispatch-mode-pill {{ isset($schedule_ride->id) ? 'active' : '' }}">Edit Booking</span>
						</div>
					</div>
					<div class="dispatch-panel-body">
						<div class="dispatch-block">
							<div class="dispatch-block-title">
								<h6>Passenger</h6>
								<span class="dispatch-inline-hint">Fast rider capture</span>
							</div>

							<div class="dispatch-customer-status mb-3">
								<span class="dispatch-status-pill success">{{ $rideUser->id ? 'Existing Passenger' : 'New Passenger' }}</span>
								<span class="dispatch-status-pill">VIP Placeholder</span>
								<span class="dispatch-status-pill warning">Blocked Placeholder</span>
							</div>

							<div class="dispatch-form-grid single">
								<div class="cls_manualbook" ng-init="country_code = '{{ $rideCountryCode }}'">
									<label class="form-label" for="input_country_code">Country</label>
									<select class ='form-control selectpicker' data-live-search="true" id="input_country_code" name='country_code' ng-model="country_code">
										@foreach($country_code_option as $country_code)
										<option value="{{@$country_code->phone_code}}" data-id="{{ $country_code->id }}" {{ ($country_code->id == (old('country_id')!=null? old('country_id') : '1')) ? 'selected' : ''  }}>{{$country_code->long_name}}</option>
										@endforeach
										{!! Form::hidden('country_id', old('country_id'), array('id'=>'country_id')) !!}
									</select>
									<span class="text-danger error_msg">{{ $errors->first('country_code') }}</span>
								</div>

								<div>
									<label class="form-label" for="input_mobile_number">Phone Number</label>
									<div class="dispatch-split-phone number-field">
										<input type="text" disabled name="country_code_view" class='form-control text-center' id='country_code_view'>
										<div>
											{!! Form::text('mobile_number', $rideUser->mobile_number, ['class' => 'form-control', 'id' => 'input_mobile_number', 'placeholder' => 'Phone No', 'autocomplete' => 'off']) !!}
										</div>
									</div>
									<span class="text-danger error_msg">{{ $errors->first('mobile_number') }}</span>
								</div>
							</div>

							<div class="dispatch-form-grid mt-3">
								<div>
									<label class="form-label" for="input_first_name">First Name <span class="dispatch-shortcut">F4</span></label>
									{!! Form::text('first_name', $rideUser->first_name, ['class' => 'form-control', 'id' => 'input_first_name', 'placeholder' => 'First Name', 'autocomplete' => 'off']) !!}
									<span class="text-danger error_msg">{{ $errors->first('first_name') }}</span>
								</div>
								<div>
									<label class="form-label" for="input_last_name">Last Name</label>
									{!! Form::text('last_name', $rideUser->last_name, ['class' => 'form-control', 'id' => 'input_last_name', 'placeholder' => 'Last Name', 'autocomplete' => 'off']) !!}
									<span class="text-danger error_msg">{{ $errors->first('last_name') }}</span>
								</div>
								<div>
									<label class="form-label" for="input_email">Email</label>
									{!! Form::text('email', $rideUser->email, ['class' => 'form-control', 'id' => 'input_email', 'placeholder' => 'Email', 'autocomplete' => 'off']) !!}
									<span class="text-danger error_msg">{{ $errors->first('email') }}</span>
								</div>
								<div>
									<label class="form-label" for="input_gender">Gender</label>
									<select name="gender" class="form-select" id="input_gender" ng-model="gender">
										<option value="">Gender</option>
										<option value="1">Male</option>
										<option value="2">Female</option>
									</select>
								</div>
							</div>

							<div class="mt-3">
								<label class="form-label" for="input_filter">Passenger Preferences</label>
								<div class="dispatch-preferences mb-2">Accessibility and special request filters stay compatible with the current Angular and backend workflow.</div>
								<select name="filter[]" multiple="multiple" class="form-control" id="input_filter" style="display: none;">
									<option value="2">Prefer Handicap Accessibility</option>
									<option value="3">Prefer Child Seat Accessibility</option>
									<option value="4">Prefer Female Drivers only</option>
								</select>
							</div>
						</div>

						<div class="dispatch-block">
							<div class="dispatch-block-title">
								<h6>Trip</h6>
								<span class="dispatch-inline-hint">Pickup, route, timing</span>
							</div>

							<div class="dispatch-form-grid mb-3">
								<div>
									<label class="form-label">Booking Mode</label>
									<div class="dispatch-mode-pills" id="dispatch-booking-mode-pills">
										<button type="button" class="dispatch-mode-pill {{ (($ride->booking_mode ?: 'asap') === 'asap') ? 'active' : '' }}" data-booking-mode="asap">ASAP / Book Now</button>
										<button type="button" class="dispatch-mode-pill {{ ($ride->booking_mode === 'scheduled') ? 'active' : '' }}" data-booking-mode="scheduled">Scheduled / Later</button>
									</div>
								</div>
								<div class="soft-panel">
									<div class="dispatch-checkbox-row">
										<div>
											<div class="fw-bold">Dispatch Override</div>
											<small class="text-muted">Allow close pickup times when policy permits.</small>
										</div>
										<label class="switch mb-0">
											<input type="checkbox" id="dispatch-override-toggle" {{ $ride->is_dispatch_override ? 'checked' : '' }}>
											<span class="slider round"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="dispatch-form-grid single">
								<div ng-init='pickup_latitude = "{{$ride->pickup_latitude}}"'>
									<label class="form-label" for="input_pickup_location">Pick Up Location <span class="dispatch-shortcut">F2</span></label>
									{!! Form::hidden('pickup_latitude', $ride->pickup_latitude, ['id' => 'pickup_latitude']) !!}
									{!! Form::hidden('pickup_longitude', $ride->pickup_longitude, ['id' => 'pickup_longitude']) !!}
									{!! Form::text('pickup_location', $ride->pickup_location, ['class' => 'form-control change_field', 'id' => 'input_pickup_location', 'placeholder' => 'Pick Up Location', 'autocomplete' => 'off']) !!}
									<span class="text-danger error_msg error_pickup_location">{{ $errors->first('pickup_location') }}</span>
								</div>

								<div>
									<label class="form-label" for="input_drop_location">Drop Off Location <span class="dispatch-shortcut">F3</span></label>
									{!! Form::hidden('drop_latitude', $ride->drop_latitude, ['id' => 'drop_latitude']) !!}
									{!! Form::hidden('drop_longitude', $ride->drop_longitude, ['id' => 'drop_longitude']) !!}
									<div class="dispatch-actions-inline">
										{!! Form::text('drop_location', $ride->drop_location, ['class' => 'form-control change_field flex-grow-1', 'id' => 'input_drop_location', 'placeholder' => 'Drop Off Location', 'autocomplete' => 'off']) !!}
										<button type="button" class="dispatch-ghost-btn" id="dispatch-swap-route" title="Swap pickup and drop">
											<i class="bi bi-arrow-left-right"></i>
										</button>
										<button type="button" class="dispatch-ghost-btn" title="Add waypoint placeholder">
											<i class="bi bi-plus-lg"></i>
											Add Via
										</button>
									</div>
									<span class="text-danger error_msg error_drop_location">{{ $errors->first('drop_location') }}</span>
								</div>
							</div>

							<div class="dispatch-form-grid mt-3">
								<div ng-init='date_time = "{{$ride->schedule_date}} {{$ride->schedule_time}}"'>
									<label class="form-label" for="input_date_time">Date / Time</label>
									{!! Form::text('date_time',($ride->schedule_date==null)?'':$ride->schedule_date.' '.$ride->schedule_time, ['class' => 'form-control change_field', 'id' => 'input_date_time', 'placeholder' => 'Select Date/Time Location','ng-cloak','disabled'=>isset($schedule_ride->id)?false:true]) !!}
									<span class="text-danger error_msg"></span>
								</div>

								<div ng-init="vehicle_type_value = '{{$ride->car_id}}'">
									<label class="form-label" for="input_vehicle_type">Vehicle Type</label>
									<select class='form-control change_field' ng-cloak placeholder ='Select Vehicle Type' name="vehicle_type" id="input_vehicle_type" ng-model="vehicle_type_value" ng-change="list_driver()" disabled>
										<option value="" disabled>Select Vehicle Type</option>
										<option ng-repeat="(key,vehicle_type) in vehicle_types" value="@{{vehicle_type.id}}" key=@{{key}} @if(isset($schedule_ride->id)) ng-selected="@{{key}} == @{{vehicle_type_value}}" @endif>@{{vehicle_type.car_name}}</option>
									</select>
									<span class="text-danger error_msg error_vehicle_type"></span>
								</div>
							</div>

							<div class="dispatch-form-grid mt-3">
								<div>
									<label class="form-label" for="dispatch-priority-selector">Priority</label>
									<select class="form-select" id="dispatch-priority-selector" name="priority">
										<option value="normal" {{ ($ride->priority ?: 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
										<option value="priority" {{ $ride->priority === 'priority' ? 'selected' : '' }}>Priority</option>
										<option value="account" {{ $ride->priority === 'account' ? 'selected' : '' }}>Account</option>
										<option value="airport" {{ $ride->priority === 'airport' ? 'selected' : '' }}>Airport</option>
										<option value="vip" {{ $ride->priority === 'vip' ? 'selected' : '' }}>VIP</option>
									</select>
								</div>
								<div>
									<label class="form-label" for="dispatch-driver-note">Instruction / Note</label>
									<textarea class="form-control" id="dispatch-driver-note" name="dispatcher_note" placeholder="Add operator note for this booking.">{{ old('dispatcher_note', $ride->dispatcher_note) }}</textarea>
								</div>
							</div>
						</div>

						<div class="dispatch-block">
							<div class="dispatch-block-title">
								<h6>Assignment</h6>
								<span class="dispatch-inline-hint">Driver matching controls</span>
							</div>

							<div class="dispatch-checkbox-row" ng-init="auto_assign_status = {{(($ride->driver_id==0 || $ride->driver_id==null) && $ride->id != '')?'true':'false'}}">
								<div class="soft-panel flex-grow-1">
									<p id="auto_assign_status_error" class="mb-0">
										<input class="change_field" id="input_auto_assign_status" ng-model="auto_assign_status" type="checkbox" name="auto_assign_status" data-error-placement="container" data-error-container="#auto_assign_status_error">
										Auto Assign Driver
										<br>
										<span class="text-danger error_msg"></span>
									</p>
								</div>
								<div class="dispatch-status-pill success">Manual Assign Enabled</div>
							</div>

							<div class="mt-3">
								<label class="form-label">Dispatch Mode</label>
								<div class="dispatch-mode-pills" id="dispatch-mode-pills">
									<button type="button" class="dispatch-mode-pill {{ ($ride->dispatch_mode ?: (($ride->driver_id ?? 0) ? 'manual' : 'auto')) === 'manual' ? 'active' : '' }}" data-dispatch-mode="manual">Manual Assign</button>
									<button type="button" class="dispatch-mode-pill {{ ($ride->dispatch_mode === 'auto' || (!$ride->dispatch_mode && !($ride->driver_id ?? 0))) ? 'active' : '' }}" data-dispatch-mode="auto">Auto Assign</button>
									<button type="button" class="dispatch-mode-pill {{ $ride->dispatch_mode === 'broadcast' ? 'active' : '' }}" data-dispatch-mode="broadcast">Broadcast</button>
									<button type="button" class="dispatch-mode-pill {{ $ride->dispatch_mode === 'sequential' ? 'active' : '' }}" data-dispatch-mode="sequential">Sequential Offer</button>
									<button type="button" class="dispatch-mode-pill {{ $ride->dispatch_mode === 'force' ? 'active' : '' }}" data-dispatch-mode="force">Force Assign</button>
								</div>
							</div>

							<div class="dispatch-form-grid mt-3">
								<div class="soft-panel">
									<div class="dispatch-checkbox-row">
										<div>
											<div class="fw-bold">Assign / Dispatch Now</div>
											<small class="text-muted">For scheduled rides, this sends the request immediately instead of waiting for the pre-dispatch window.</small>
										</div>
										<label class="switch mb-0">
											<input type="checkbox" id="dispatch-assign-now-toggle" {{ $ride->assign_now || (($ride->booking_mode ?: 'asap') === 'asap') ? 'checked' : '' }}>
											<span class="slider round"></span>
										</label>
									</div>
								</div>
								<div>
									<label class="form-label" for="dispatch-driver-note-field">Driver Note</label>
									<textarea class="form-control" id="dispatch-driver-note-field" name="driver_note" placeholder="Optional note visible to driver if supported.">{{ old('driver_note', $ride->driver_note) }}</textarea>
								</div>
							</div>

							<div class="assigned_driver mt-3" style="display: none">
								<div class="col-md-12" ng-cloak style="background-color: var(--color-as-222c31);color: white">
									Assigned Driver: @{{assigned_driver.first_name}} @{{assigned_driver.hidden_mobile_number}}
								</div>
							</div>

							<div class="dispatch-form-grid mt-3">
								<div class="dispatch-driver-search">
									<label class="form-label" for="input_search_driver">Search Driver</label>
									{!! Form::text('search_driver', '', ['class' => 'form-control', 'id' => 'input_search_driver', 'placeholder' => 'Type driver name to search from below list','ng-model'=>'search_driver']) !!}
									<span class="dispatch-shortcut">F6</span>
									<span class="text-danger error_msg"></span>
								</div>
								<div ng-init="driver_availability = ''">
									<label class="form-label" for="input_driver_availability">Availability</label>
									<select class='form-control change_field' id='input_driver_availability' name='driver_availability' ng-model="driver_availability" ng-change="list_driver()">
									</select>
									<span class="text-danger error_msg">{{ $errors->first('driver_availability') }}</span>
								</div>
								<div ng-init="map_radius={{ (int) ($dispatch_settings['default_radius_miles'] ?? 10) }}">
									<label class="form-label" for="input_map_zoom">Map Radius</label>
									<select class='form-control' id='input_map_zoom' name='map_zoom' ng-model="map_radius" ng-change="map_zoom(map_radius)">
										<option value="0">Select Radius</option>
										<option value="5">5 Miles Radius</option>
										<option value="10">10 Miles Radius</option>
										<option value="20">20 Miles Radius</option>
										<option value="30">30 Miles Radius</option>
									</select>
									<span class="text-danger error_msg">{{ $errors->first('map_zoom') }}</span>
								</div>
								<div>
									<label class="form-label">Dispatch Readiness</label>
									<div class="dispatch-checklist">
										<div class="dispatch-check-item {{ !empty($rideUser->first_name) ? 'complete' : 'pending' }}">
											<span class="dispatch-check-dot"></span>
											Passenger
										</div>
										<div class="dispatch-check-item pending" id="dispatch-check-pickup">
											<span class="dispatch-check-dot"></span>
											Pickup
										</div>
										<div class="dispatch-check-item pending" id="dispatch-check-drop">
											<span class="dispatch-check-dot"></span>
											Drop-off
										</div>
										<div class="dispatch-check-item pending" id="dispatch-check-vehicle">
											<span class="dispatch-check-dot"></span>
											Vehicle
										</div>
										<div class="dispatch-check-item pending" id="dispatch-check-fare">
											<span class="dispatch-check-dot"></span>
											Fare
										</div>
										<div class="dispatch-check-item pending" id="dispatch-check-assignment">
											<span class="dispatch-check-dot"></span>
											Driver / Auto Assign
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="dispatch-map-panel">
				<div class="dispatch-panel-card">
					<div class="dispatch-panel-head">
						<div>
							<h5 class="dispatch-panel-title">Map, Route & Dispatch Intelligence</h5>
							<p class="dispatch-panel-subtitle">Keep the route engine and driver markers central to the operator experience.</p>
						</div>
					</div>
					<div class="dispatch-panel-body">
						<div class="dispatch-map-topbar">
							<div class="dispatch-map-stat">
								<span class="dispatch-map-stat-label">Pickup ETA</span>
								<span class="dispatch-map-stat-value">@{{vehicle_detail_minutes || 0}} min</span>
							</div>
							<div class="dispatch-map-stat">
								<span class="dispatch-map-stat-label">Route Distance</span>
								<span class="dispatch-map-stat-value">@{{vehicle_detail_km || 0}} km</span>
							</div>
							<div class="dispatch-map-stat">
								<span class="dispatch-map-stat-label">Nearby Drivers</span>
								<span class="dispatch-map-stat-value">@{{(drivers && drivers.length) || 0}}</span>
							</div>
							<div class="dispatch-map-stat">
								<span class="dispatch-map-stat-label">Available Drivers</span>
								<span class="dispatch-map-stat-value">@{{(drivers | filter:{ driver_current_status: driver_availability }).length || 0}}</span>
							</div>
						</div>

						<div class="dispatch-map-toolbar">
							<div class="dispatch-map-tabs">
								<button type="button" class="dispatch-map-tab active" data-map-mode="map">Map</button>
								<button type="button" class="dispatch-map-tab" data-map-mode="parking">Parking</button>
								<button type="button" class="dispatch-map-tab" data-map-mode="heatmap">Heatmap</button>
								<button type="button" class="dispatch-map-tab" data-map-mode="zones">Zones</button>
								<button type="button" class="dispatch-map-tab" data-map-mode="route">Route</button>
							</div>
							<div class="dispatch-map-controls">
								<button type="button" class="dispatch-ghost-btn" id="dispatch-recenter-map">
									<i class="bi bi-crosshair"></i>
									Recenter
								</button>
								<button type="button" class="dispatch-ghost-btn" id="dispatch-fit-route">
									<i class="bi bi-aspect-ratio"></i>
									Fit Route
								</button>
								<button type="button" class="dispatch-ghost-btn" id="dispatch-refresh-drivers">
									<i class="bi bi-arrow-repeat"></i>
									Refresh Drivers
								</button>
								<button type="button" class="dispatch-ghost-btn" id="dispatch-clear-route">
									<i class="bi bi-x-circle"></i>
									Clear Route
								</button>
							</div>
						</div>

						<div class="dispatch-map-filter-grid mb-3">
							<div class="soft-panel">
								<label class="form-label mb-2" for="input_driver_availability_clone">Driver Availability</label>
								<select class='form-control change_field' id='input_driver_availability_clone' ng-model="driver_availability" ng-change="list_driver()">
									<option value="">All</option>
									<option value="Online">Available Driver</option>
									<option value="Scheduled">Enroute to Pickup</option>
									<option value="Begin trip">Reached Pickup</option>
									<option value="End trip">Journey Started</option>
									<option value="Offline">Offline</option>
								</select>
							</div>
							<div class="soft-panel">
								<label class="form-label mb-2" for="input_map_zoom_clone">Map Radius</label>
								<select class='form-control' id='input_map_zoom_clone' ng-model="map_radius" ng-change="map_zoom(map_radius)">
									<option value="0">Select Radius</option>
									<option value="5">5 Miles Radius</option>
									<option value="10">10 Miles Radius</option>
									<option value="20">20 Miles Radius</option>
									<option value="30">30 Miles Radius</option>
								</select>
							</div>
						</div>

						<div class="map-view clearfix">
							<div class="dispatch-map-overlay">
								<button type="button" class="dispatch-ghost-btn" id="dispatch-map-focus-pickup">Focus Pickup</button>
								<button type="button" class="dispatch-ghost-btn" id="dispatch-map-focus-drop">Focus Drop</button>
							</div>
							<div class="clearfix driver_detail_popup" style="width:100%;background:white;display:none;position: absolute;z-index: 100">
								<h4>Driver Details</h4>
								<div class="row">
									<div class="col-lg-4">
										Driver :
									</div>
									<div class="col-lg-8 driver_name_detail">
									</div>
								</div>
								@if(LOGIN_USER_TYPE!='company')
									<div class="row driver_company_show">
										<div class="col-lg-4">
											Company :
										</div>
										<div class="col-lg-8 driver_company_detail">
										</div>
									</div>
								@endif
								<div class="row">
									<div class="col-lg-4">
										Driver Email :
									</div>
									<div class="col-lg-8 driver_email_detail">
									</div>
								</div>
								<div class="row">
									<div class="col-lg-4">
										Phone No :
									</div>
									<div class="col-lg-8 driver_phone_detail">
									</div>
								</div>
							</div>
							<div id="map"></div>
						</div>
					</div>
				</div>
			</div>

			<div class="dispatch-side-panel">
				<div class="dispatch-panel-card mb-3">
					<div class="dispatch-panel-head">
						<div>
							<h5 class="dispatch-panel-title">Recommended Driver</h5>
							<p class="dispatch-panel-subtitle">Top candidate based on current list ordering and operator filters.</p>
						</div>
					</div>
					<div class="dispatch-panel-body">
						<div class="dispatch-driver-recommended" ng-cloak>
							<p class="dispatch-driver-name mb-1">@{{(drivers && drivers[0] && drivers[0].first_name) || 'No driver selected yet'}}</p>
							<p class="dispatch-driver-meta mb-2">
								Phone: @{{(drivers && drivers[0] && drivers[0].mobile_number) || 'N/A'}}<br>
								Vehicle: @{{(drivers && drivers[0] && drivers[0].vehicle_number) || 'Vehicle info pending'}}<br>
								Distance: @{{(drivers && drivers[0] && drivers[0].distance_km) || 0}} km<br>
								Status: @{{(drivers && drivers[0] && drivers[0].driver_current_status) || 'Unknown'}}<br>
								Dispatch mode: {{ $ride->dispatch_mode ?: 'auto' }}
							</p>
							<div class="dispatch-actions-inline">
								<button type="button" class="btn btn-success" ng-click="drivers && drivers[0] && auto_assign(drivers[0].id)">
									Assign
								</button>
								@if(LOGIN_USER_TYPE!='company')
								<span class="dispatch-status-pill">Company View</span>
								@endif
							</div>
						</div>
					</div>
				</div>

				<div class="dispatch-panel-card mb-3">
					<div class="dispatch-panel-head">
						<div>
							<h5 class="dispatch-panel-title">Driver List</h5>
							<p class="dispatch-panel-subtitle">Scan, compare, and assign available drivers quickly.</p>
						</div>
						<div style="display: none;" class="loader"></div>
					</div>
					<div class="dispatch-panel-body">
						<div class="driver_list_div">
							<div style="display: none;" class="driver_list">
								<div class="dispatch-driver-card row clearfix" ng-repeat="driver in drivers | filter:Driverfilter(search_driver) | filter:{ driver_current_status: driver_availability }">
									<span class="driver_detail_view" id=@{{driver.id}} first_name=@{{driver.first_name}} last_name=@{{driver.last_name}} phone=@{{driver.mobile_number}} email=@{{driver.email}} company=@{{driver.company}}>
										<img ng-src="@{{driver.src}}" class="dispatch-driver-avatar" alt="@{{driver.first_name}}">
										<div>
											<div class="dispatch-actions-inline mb-1">
												<p class="dispatch-driver-name">@{{driver.first_name}} @{{driver.last_name}}</p>
												<span class="dispatch-driver-status"
													ng-class="{
														'available': driver.driver_current_status == '' || driver.driver_current_status == 'Available',
														'busy': driver.driver_current_status == 'Trip' || driver.driver_current_status == 'Pool Trip',
														'offline': driver.driver_current_status == 'Offline',
														'assigned': assigned_driver && assigned_driver.id == driver.id,
														'nearby': driver.driver_current_status == 'Online'
													}">
													@{{driver.driver_current_status || 'Available'}}
												</span>
											</div>
											<p class="dispatch-driver-meta">
												Phone: @{{driver.mobile_number}}<br>
												@if(LOGIN_USER_TYPE!='company')
												Company: @{{driver.company}}<br>
												@endif
												Vehicle: @{{driver.vehicle_type || 'N/A'}} / @{{driver.vehicle_number || 'N/A'}}<br>
												Distance: @{{driver.distance_km || 0}} km
											</p>
										</div>
										<div class="dispatch-driver-actions">
											<a href="{{ url(LOGIN_USER_TYPE.'/edit_driver') }}/@{{driver.id}}" target="_blank" class="btn btn-light">View</a>
											<a href="tel:@{{driver.mobile_number}}" class="btn btn-light">Call</a>
											<button type="button" class="btn btn-primary" ng-click="auto_assign(driver.id)">Assign</button>
										</div>
									</span>
								</div>
							</div>
						</div>
						<div class="dispatch-validation-area mt-3">
							Use search, availability, and route context to choose the most suitable driver. Assignment remains fully compatible with the existing AngularJS flow.
						</div>
					</div>
				</div>

				<div class="dispatch-panel-card">
					<div class="dispatch-panel-head">
						<div>
							<h5 class="dispatch-panel-title">Fare Summary</h5>
							<p class="dispatch-panel-subtitle">Live estimate based on selected vehicle, route, time and peak pricing.</p>
						</div>
					</div>
					<div class="dispatch-panel-body">
						<div class="fare-table clearfix">
							<h3>Fare Estimation</h3>
							<p class="fare-subcopy">Fare engine remains driven by your existing Angular variables and backend pricing logic.</p>
							<div class="dispatch-peak-pill" ng-if="vehicle_detail_peak_price > 1" ng-cloak>
								<i class="bi bi-exclamation-triangle"></i>
								Peak multiplier active
							</div>
							<div class="row">
								<div class="col-7">Minimum Fare</div>
								<div class="col-5 text-end" ng-cloak>{{$currency_symbol}}@{{vehicle_detail_minimum_fare | number: 2}}</div>
							</div>
							<div class="row">
								<div class="col-7">Base Fare</div>
								<div class="col-5 text-end" ng-cloak>{{$currency_symbol}}@{{vehicle_detail_base_fare | number: 2}}</div>
							</div>
							<div class="row">
								<div class="col-7">Distance Fare <small class="text-muted d-block">@{{vehicle_detail_km}} km</small></div>
								<div class="col-5 text-end" ng-cloak>{{$currency_symbol}}@{{vehicle_detail_km_fare | number: 2}}</div>
							</div>
							<div class="row">
								<div class="col-7">Time Fare <small class="text-muted d-block">@{{vehicle_detail_minutes}} min</small></div>
								<div class="col-5 text-end" ng-cloak>{{$currency_symbol}}@{{vehicle_detail_min_fare | number: 2}}</div>
							</div>
							<div class="row">
								<div class="col-7">Peak Pricing <small class="text-muted d-block">x @{{vehicle_detail_peak_price}}</small></div>
								<div class="col-5 text-end" ng-cloak>{{$currency_symbol}}@{{(vehicle_detail_peak_fare) | number: 2}}</div>
							</div>

							<div class="dispatch-total-fare" ng-cloak>
								<small>Total Fare</small>
								<strong>{{$currency_symbol}}@{{vehicle_detail_total_fare | number: 2}}</strong>
							</div>
						</div>

						<div class="dispatch-sticky-actions">
							<div class="dispatch-sticky-bar">
								<div class="dispatch-sticky-summary">
									<div class="dispatch-sticky-summary-item">
										<small>Status</small>
										<strong>{{ isset($schedule_ride->id) ? 'Editing Scheduled Booking' : 'Ready for New Booking' }}</strong>
									</div>
									<div class="dispatch-sticky-summary-item" ng-cloak>
										<small>Assigned Driver</small>
										<strong>@{{assigned_driver.first_name || 'Auto / Pending'}}</strong>
									</div>
									<div class="dispatch-sticky-summary-item" ng-cloak>
										<small>Estimate</small>
										<strong>{{$currency_symbol}}@{{vehicle_detail_total_fare | number: 2}}</strong>
									</div>
								</div>
								<div class="dispatch-actions-inline">
									<button type="button" class="btn btn-primary submit_button change_field" disabled ng-disabled="myForm.$invalid && page_loading==0" ng-click="submitForm($event);">
										@if(!isset($schedule_ride->id))
											Book Now
										@else
											Submit Changes
										@endif
									</button>
									@if(!isset($schedule_ride->id))
									<button type="button" class="btn btn-outline-light reset">Reset</button>
									@endif
									<button type="button" class="btn btn-outline-light" disabled>Save Draft</button>
								</div>
							</div>
						</div>

						<div class="fare-btn clearfix">
							<button type="button" class="btn btn-primary submit_button change_field" disabled ng-disabled="myForm.$invalid && page_loading==0" ng-click="submitForm($event);">
								@if(!isset($schedule_ride->id))
									Book Now
								@else
									Submit Changes
								@endif
							</button>
							@if(!isset($schedule_ride->id))
							<button class="btn btn-light reset">Reset</button>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="dispatch-queue-panel">
			<div class="dispatch-panel-card">
				<div class="dispatch-panel-head">
					<div>
						<h5 class="dispatch-panel-title">Live Booking Queue</h5>
						<p class="dispatch-panel-subtitle">A queue-style shell for dispatch operations, ready to connect to your existing booking list API or screens.</p>
					</div>
				</div>
				<div class="dispatch-panel-body">
					<ul class="nav nav-pills mb-3" role="tablist">
						<li class="nav-item"><a class="nav-link" href="{{ url(LOGIN_USER_TYPE.'/later_booking?queue=asap') }}">ASAP <span class="badge bg-light text-dark">{{ $dispatch_queue_counts['asap'] ?? 0 }}</span></a></li>
						<li class="nav-item"><a class="nav-link" href="{{ url(LOGIN_USER_TYPE.'/later_booking?queue=scheduled') }}">Scheduled <span class="badge bg-light text-dark">{{ $dispatch_queue_counts['scheduled'] ?? 0 }}</span></a></li>
						<li class="nav-item"><a class="nav-link" href="{{ url(LOGIN_USER_TYPE.'/later_booking?queue=assigned') }}">Assigned <span class="badge bg-light text-dark">{{ $dispatch_queue_counts['assigned'] ?? 0 }}</span></a></li>
						<li class="nav-item"><a class="nav-link" href="{{ url(LOGIN_USER_TYPE.'/later_booking?queue=dispatch_due') }}">Dispatch Due <span class="badge bg-light text-dark">{{ $dispatch_queue_counts['dispatch_due'] ?? 0 }}</span></a></li>
						<li class="nav-item"><a class="nav-link" href="{{ url(LOGIN_USER_TYPE.'/later_booking?queue=completed') }}">Completed <span class="badge bg-light text-dark">{{ $dispatch_queue_counts['completed'] ?? 0 }}</span></a></li>
						<li class="nav-item"><a class="nav-link" href="{{ url(LOGIN_USER_TYPE.'/later_booking?queue=cancelled') }}">Cancelled <span class="badge bg-light text-dark">{{ $dispatch_queue_counts['cancelled'] ?? 0 }}</span></a></li>
						<li class="nav-item"><a class="nav-link" href="{{ url(LOGIN_USER_TYPE.'/later_booking?queue=no_show') }}">No Show <span class="badge bg-light text-dark">{{ $dispatch_queue_counts['no_show'] ?? 0 }}</span></a></li>
					</ul>

					<div class="dispatch-queue-empty">
						<strong>Queue shortcuts are now linked to the existing scheduled booking screen.</strong><br>
						Use these tabs to jump into filtered dispatch queues without changing the current backend route structure.
					</div>

					<button class="dispatch-operational-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#dispatchOperationalRules" aria-expanded="false" aria-controls="dispatchOperationalRules">
						<span>Operational Rules</span>
						<i class="bi bi-chevron-down"></i>
					</button>
					<div class="collapse" id="dispatchOperationalRules">
						<div class="manual-notes">
							<h4>Notes:</h4>
							<ul>
								<li>Administrator can create ASAP or Scheduled bookings from the same console.</li>
								<li>Scheduled lead time and pre-dispatch timing now follow configurable dispatch settings instead of a fixed 15-minute or 5-minute rule.</li>
								<li>Dispatch override can allow close-time bookings when policy permits.</li>
							</ul>
							<h5>Manual Auto Booking :</h5>
							<ul>
								<li>Auto assign dispatch timing is driven by the scheduled dispatch lead minutes setting.</li>
								<li>Manual assign can either hold the booking until dispatch time or be dispatched immediately using the "Assign / Dispatch Now" switch.</li>
								<li>
									Driver has to start the scheduled Trip by going to "Your Trip" >> Upcoming section from Driver App.
								</li>
								<li>
									Broadcast and sequential dispatch modes remain compatible with the current request engine while storing the operator's chosen dispatch mode.
								</li>
							</ul>
							<h5>Manual Assign Booking :</h5>
							<ul>
								<li>
									Please confirm future avaialbility of Driver before doing booking.
								</li>
								<li>
									Driver has to start the scheduled Trip by going to "Your Trip" >> Upcoming section from Driver App.
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		{!! Form::close() !!}
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
	var REQUEST_URL = "{{url('/'.LOGIN_USER_TYPE)}}"; 
	var old_edit_date = "{{isset($schedule_ride->id)?$ride->schedule_date.' '.$ride->schedule_time:''}}";
	var page = "{{isset($schedule_ride->id)?'edit':'new'}}";
	var MANUAL_DISPATCH_SETTINGS = @json($dispatch_settings);

	(function () {
		function getConsoleApi() {
			return window.manualBookingConsole || null;
		}

		function focusIfExists(id) {
			var element = document.getElementById(id);
			if (element && !element.disabled) {
				element.focus();
				if (typeof element.select === 'function') {
					element.select();
				}
			}
		}

		function swapPickupDrop() {
			var pickup = document.getElementById('input_pickup_location');
			var drop = document.getElementById('input_drop_location');
			var pickupLat = document.getElementById('pickup_latitude');
			var pickupLng = document.getElementById('pickup_longitude');
			var dropLat = document.getElementById('drop_latitude');
			var dropLng = document.getElementById('drop_longitude');

			if (!pickup || !drop) {
				return;
			}

			var pickupValue = pickup.value;
			var dropValue = drop.value;
			pickup.value = dropValue;
			drop.value = pickupValue;

			if (pickupLat && dropLat) {
				var tempLat = pickupLat.value;
				pickupLat.value = dropLat.value;
				dropLat.value = tempLat;
			}

			if (pickupLng && dropLng) {
				var tempLng = pickupLng.value;
				pickupLng.value = dropLng.value;
				dropLng.value = tempLng;
			}

			if (window.jQuery) {
				window.jQuery(pickup).trigger('change');
				window.jQuery(drop).trigger('change');
			}
		}

		function updateModeButtons(mode) {
			document.querySelectorAll('[data-map-mode]').forEach(function (button) {
				button.classList.toggle('active', button.getAttribute('data-map-mode') === mode);
			});
		}

		function populateFormAlert() {
			var alertBox = document.getElementById('dispatch-form-alert');
			if (!alertBox) {
				return;
			}

			var hasErrors = Array.from(document.querySelectorAll('#manual_booking span.text-danger, #manual_booking span.error')).some(function (node) {
				return node.textContent && node.textContent.trim() !== '';
			});
			alertBox.classList.toggle('d-none', !hasErrors);
		}

		function updateChecklist() {
			var pickup = document.getElementById('input_pickup_location');
			var drop = document.getElementById('input_drop_location');
			var vehicle = document.getElementById('input_vehicle_type');
			var manualBookingId = document.getElementById('manual_booking_id');
			var passenger = document.getElementById('input_first_name');
			var autoAssign = document.getElementById('input_auto_assign_status');
			var fareNode = document.querySelector('.dispatch-total-fare strong');

			var checks = {
				'dispatch-check-pickup': pickup && pickup.value.trim() !== '',
				'dispatch-check-drop': drop && drop.value.trim() !== '',
				'dispatch-check-vehicle': vehicle && vehicle.value !== '',
				'dispatch-check-fare': fareNode && fareNode.textContent.replace(/\s/g, '') !== '' && !/0\.00$/.test(fareNode.textContent.trim()),
				'dispatch-check-assignment': (autoAssign && autoAssign.checked) || (document.getElementById('auto_assign_id') && document.getElementById('auto_assign_id').value !== '' && document.getElementById('auto_assign_id').value !== '0'),
			};

			var passengerNode = passenger ? passenger.closest('.dispatch-booking-panel') : null;
			if (passengerNode) {
				var passengerCheck = passenger.value.trim() !== '' || (manualBookingId && manualBookingId.value !== '');
				var passengerItem = document.querySelector('.dispatch-check-item');
				if (passengerItem) {
					passengerItem.classList.toggle('complete', passengerCheck);
					passengerItem.classList.toggle('pending', !passengerCheck);
				}
			}

			Object.keys(checks).forEach(function (id) {
				var node = document.getElementById(id);
				if (!node) {
					return;
				}
				node.classList.toggle('complete', checks[id]);
				node.classList.toggle('pending', !checks[id]);
			});
		}

		function syncDispatchModeUI(mode) {
			document.querySelectorAll('[data-dispatch-mode]').forEach(function (button) {
				button.classList.toggle('active', button.getAttribute('data-dispatch-mode') === mode);
			});

			document.getElementById('dispatch_mode').value = mode;
			var autoAssignToggle = document.getElementById('input_auto_assign_status');
			if (!autoAssignToggle) {
				return;
			}

			var shouldAutoAssign = ['auto', 'broadcast', 'sequential'].indexOf(mode) !== -1;
			autoAssignToggle.checked = shouldAutoAssign;
			if (window.jQuery) {
				window.jQuery(autoAssignToggle).trigger('change');
			}
		}

		function syncBookingModeUI(mode) {
			document.querySelectorAll('[data-booking-mode]').forEach(function (button) {
				button.classList.toggle('active', button.getAttribute('data-booking-mode') === mode);
			});

			document.getElementById('booking_mode').value = mode;

			var assignNowToggle = document.getElementById('dispatch-assign-now-toggle');
			if (assignNowToggle && mode === 'asap') {
				assignNowToggle.checked = true;
				document.getElementById('assign_now').value = '1';
			}
		}

		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('[data-booking-mode]').forEach(function (button) {
				button.addEventListener('click', function () {
					syncBookingModeUI(button.getAttribute('data-booking-mode'));
					updateChecklist();
				});
			});

			document.querySelectorAll('[data-dispatch-mode]').forEach(function (button) {
				button.addEventListener('click', function () {
					syncDispatchModeUI(button.getAttribute('data-dispatch-mode'));
					updateChecklist();
				});
			});

			var overrideToggle = document.getElementById('dispatch-override-toggle');
			if (overrideToggle) {
				overrideToggle.addEventListener('change', function () {
					document.getElementById('is_dispatch_override').value = overrideToggle.checked ? '1' : '0';
				});
			}

			var assignNowToggle = document.getElementById('dispatch-assign-now-toggle');
			if (assignNowToggle) {
				assignNowToggle.addEventListener('change', function () {
					document.getElementById('assign_now').value = assignNowToggle.checked ? '1' : '0';
				});
			}

			var swapButton = document.getElementById('dispatch-swap-route');
			if (swapButton) {
				swapButton.addEventListener('click', swapPickupDrop);
			}

			var refreshDrivers = document.getElementById('dispatch-refresh-drivers');
			if (refreshDrivers) {
				refreshDrivers.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.refreshDrivers();
					}
				});
			}

			var fitRoute = document.getElementById('dispatch-fit-route');
			if (fitRoute) {
				fitRoute.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.fitRoute();
					}
					updateModeButtons('route');
				});
			}

			var recenterMap = document.getElementById('dispatch-recenter-map');
			if (recenterMap) {
				recenterMap.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.recenter();
					}
				});
			}

			var clearRoute = document.getElementById('dispatch-clear-route');
			if (clearRoute) {
				clearRoute.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.clearRoute();
					}
					updateModeButtons('map');
					updateChecklist();
				});
			}

			var focusPickup = document.getElementById('dispatch-map-focus-pickup');
			if (focusPickup) {
				focusPickup.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.focusPickup();
					}
				});
			}

			var focusDrop = document.getElementById('dispatch-map-focus-drop');
			if (focusDrop) {
				focusDrop.addEventListener('click', function () {
					var api = getConsoleApi();
					if (api) {
						api.focusDrop();
					}
				});
			}

			document.querySelectorAll('[data-map-mode]').forEach(function (button) {
				button.addEventListener('click', function () {
					var mode = button.getAttribute('data-map-mode');
					var api = getConsoleApi();
					if (api) {
						api.setMode(mode);
					}
					updateModeButtons(mode);
				});
			});

			var driverAvailabilityClone = document.getElementById('input_driver_availability_clone');
			var driverAvailabilityPrimary = document.getElementById('input_driver_availability');
			if (driverAvailabilityClone && driverAvailabilityPrimary && window.jQuery) {
				driverAvailabilityClone.addEventListener('change', function () {
					if (window.jQuery(driverAvailabilityPrimary)[0].selectize) {
						window.jQuery(driverAvailabilityPrimary)[0].selectize.setValue(driverAvailabilityClone.value, true);
					}
				});

				window.jQuery(driverAvailabilityPrimary).on('change', function () {
					driverAvailabilityClone.value = driverAvailabilityPrimary.value;
				});
			}

			var mapZoomClone = document.getElementById('input_map_zoom_clone');
			var mapZoomPrimary = document.getElementById('input_map_zoom');
			if (mapZoomClone && mapZoomPrimary) {
				mapZoomClone.addEventListener('change', function () {
					mapZoomPrimary.value = mapZoomClone.value;
					if (window.jQuery) {
						window.jQuery(mapZoomPrimary).trigger('change');
					}
				});

				mapZoomPrimary.addEventListener('change', function () {
					mapZoomClone.value = mapZoomPrimary.value;
				});
			}

			updateModeButtons('map');
			syncBookingModeUI(document.getElementById('booking_mode').value || 'asap');
			syncDispatchModeUI(document.getElementById('dispatch_mode').value || 'auto');
			document.addEventListener('keydown', function (event) {
				if (event.altKey || event.ctrlKey || event.metaKey) {
					return;
				}

				switch (event.key) {
					case 'F2':
						event.preventDefault();
						focusIfExists('input_pickup_location');
						break;
					case 'F3':
						event.preventDefault();
						focusIfExists('input_drop_location');
						break;
					case 'F4':
						event.preventDefault();
						focusIfExists('input_first_name');
						break;
					case 'F6':
						event.preventDefault();
						focusIfExists('input_search_driver');
						break;
					case 'F9':
						event.preventDefault();
						var submitButton = document.querySelector('.dispatch-sticky-bar .submit_button');
						if (submitButton && !submitButton.disabled) {
							submitButton.click();
						}
						break;
				}
			});

			['input_first_name', 'input_pickup_location', 'input_drop_location', 'input_vehicle_type', 'input_auto_assign_status'].forEach(function (id) {
				var node = document.getElementById(id);
				if (node) {
					node.addEventListener('change', updateChecklist);
					node.addEventListener('keyup', updateChecklist);
				}
			});

			populateFormAlert();
			updateChecklist();
			window.setInterval(updateChecklist, 1500);
		});
	})();
</script>
@endpush
