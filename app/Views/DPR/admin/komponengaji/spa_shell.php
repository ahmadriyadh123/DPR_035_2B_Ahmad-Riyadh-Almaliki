<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div id="app-content">
    <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
        <div class="spinner-border text-primary" role="status"></div>
        <h4 class="ms-3">Memuat...</h4>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="<?= base_url('js/utils.js') ?>"></script>
<script src="<?= base_url('js/DPR/komponengaji-app.js') ?>"></script>
<?= $this->endSection() ?>