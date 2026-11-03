<?php $this->view('messages') ?>
<div class="row">
    <div class="col-lg-4 col-sm-6 col-md-6">
        <a href="<?= site_url('member/status') ?>">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="mb-25">Paket Anda</h6>
                            <h3 class="fw-700 text-blue"><?= $CountServices ?></h3>
                            <p class="mb-0">Item Layanan</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box bg-blue"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-4 col-sm-6 col-md-6">
        <div class="card comp-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="mb-25">Tagihan <?= indo_month(date('m')) ?> <?= date('Y') ?></h6>
                        <h3 class="fw-700 text-red">-</h3>
                        <p class="mb-0">Belum Tersedia</p>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign bg-red"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
