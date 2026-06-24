<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * Bootstrap 5 pagination template.
 *
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>

<nav aria-label="<?= lang('Pager.pageNavigation') ?>">
	<ul class="pagination pagination-sm mb-0 shadow-sm">
		<li class="page-item <?= $pager->hasPrevious() ? '' : 'disabled' ?>">
			<a class="page-link" href="<?= $pager->hasPrevious() ? $pager->getFirst() : '#' ?>" aria-label="<?= lang('Pager.first') ?>">
				<i class="fas fa-angles-left"></i>
			</a>
		</li>
		<li class="page-item <?= $pager->hasPrevious() ? '' : 'disabled' ?>">
			<a class="page-link" href="<?= $pager->hasPrevious() ? $pager->getPrevious() : '#' ?>" aria-label="<?= lang('Pager.previous') ?>">
				<i class="fas fa-angle-left"></i>
			</a>
		</li>

		<?php foreach ($pager->links() as $link) : ?>
			<li class="page-item <?= $link['active'] ? 'active' : '' ?>">
				<a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
			</li>
		<?php endforeach ?>

		<li class="page-item <?= $pager->hasNext() ? '' : 'disabled' ?>">
			<a class="page-link" href="<?= $pager->hasNext() ? $pager->getNext() : '#' ?>" aria-label="<?= lang('Pager.next') ?>">
				<i class="fas fa-angle-right"></i>
			</a>
		</li>
		<li class="page-item <?= $pager->hasNext() ? '' : 'disabled' ?>">
			<a class="page-link" href="<?= $pager->hasNext() ? $pager->getLast() : '#' ?>" aria-label="<?= lang('Pager.last') ?>">
				<i class="fas fa-angles-right"></i>
			</a>
		</li>
	</ul>
</nav>
