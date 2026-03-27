/**
 * EDS Global Settings — Admin JavaScript
 * eDesign Space · https://edesignspace.com/
 *
 * Handles:
 *  - Custom variable row add / delete
 *  - Empty-state row visibility
 *  - Save notice auto-dismiss
 *  - Form active-tab tracking
 */
(function ($) {
  'use strict';

  var EDS = window.EDS_Admin || {};

  // ── Helpers ─────────────────────────────────────────────────────────────────

  /**
   * Generate a simple unique ID string (not cryptographic — just for keying).
   * Format: eds_var_ + timestamp_hex + random_4char
   *
   * @returns {string}
   */
  function generateId() {
    var ts  = Date.now().toString(16);
    var rnd = Math.floor(Math.random() * 0xffff).toString(16).padStart(4, '0');
    return 'eds_var_' + ts + rnd;
  }

  // ── Empty State ──────────────────────────────────────────────────────────────

  /**
   * Show/hide the empty-state row based on actual data rows.
   */
  function syncEmptyState() {
    var $tbody     = $('#eds-custom-tbody');
    var $empty     = $('#eds-custom-empty');
    var $dataRows  = $tbody.find('.eds-custom-row');

    if ($dataRows.length === 0) {
      if ($empty.length === 0) {
        $tbody.append(
          '<tr class="eds-custom-empty" id="eds-custom-empty">' +
            '<td colspan="5">' +
              '<span class="dashicons dashicons-plus-alt2"></span>' +
              'No custom variables yet. Click "Add Variable" to create your first one.' +
            '</td>' +
          '</tr>'
        );
      } else {
        $empty.show();
      }
    } else {
      $empty.hide();
    }
  }

  // ── Add Row ──────────────────────────────────────────────────────────────────

  /**
   * Clone the hidden template row, assign a fresh ID, and append to tbody.
   */
  function addCustomRow() {
    var tpl = document.getElementById('eds-custom-row-tpl');
    if (!tpl) return;

    // firstElementChild is the reliable way to get the <tr> from a DocumentFragment.
    var $row  = $(tpl.content.cloneNode(true).firstElementChild);
    var newId = generateId();

    $row.attr('data-id', newId);
    $row.find('.eds-custom-id').val(newId);
    $row.find('.eds-new-id').text(newId);

    $row.addClass('eds-custom-row--new');
    $row.on('animationend webkitAnimationEnd', function () {
      $(this).removeClass('eds-custom-row--new');
    });

    $('#eds-custom-tbody').append($row);
    syncEmptyState();

    $row.find('input[name="eds_custom_title[]"]').trigger('focus');
  }

  // ── Delete Row ───────────────────────────────────────────────────────────────

  /**
   * Remove a data row when the trash button is clicked.
   * Uses delegated event so it works on dynamically added rows.
   *
   * @param {jQuery.Event} e
   */
  function deleteCustomRow(e) {
    var $btn = $(e.currentTarget);
    var $row = $btn.closest('tr.eds-custom-row');

    if (!window.confirm(EDS.confirm_del || 'Delete this variable?')) {
      return;
    }

    // Short fade-out before removal.
    $row.css({ opacity: 1 }).animate({ opacity: 0 }, 180, function () {
      $(this).remove();
      syncEmptyState();
    });
  }

  // ── Save Notice ──────────────────────────────────────────────────────────────

  /**
   * Auto-dismiss the success notice after 4 s with a fade-out.
   */
  function initSaveNotice() {
    var $notice = $('#eds-save-notice');
    if ($notice.length === 0) return;

    setTimeout(function () {
      $notice.fadeOut(400);
    }, 4000);
  }

  // ── Form Tab Tracking ────────────────────────────────────────────────────────

  /**
   * Ensure the hidden active-tab field always reflects the current tab slug
   * (it's already set server-side, but we keep it in sync if JS ever changes it).
   */
  function initFormTabTracking() {
    var $tabInput = $('#eds-active-tab');
    if ($tabInput.length === 0) return;

    // Read the tab from the current URL and update the hidden field.
    var urlParams = new URLSearchParams(window.location.search);
    var tab       = urlParams.get('tab');
    if (tab) {
      $tabInput.val(tab);
    }
  }

  // ── Widget toggle — live card feedback ───────────────────────────────────────

  $(document).on('change', '.eds-toggle__input', function () {
    var $toggle  = $(this);
    var $card    = $toggle.closest('.eds-widget-card');
    var $status  = $card.find('.eds-widget-card__status');
    var $iconWrap = $card.find('.eds-widget-card__icon-wrap');
    var $icon    = $card.find('.eds-widget-card__icon');
    var enabled  = $toggle.is(':checked');

    if (enabled) {
      $card.addClass('is-enabled');
      $status.removeClass('is-off').addClass('is-on');
      $status.find('span:last-child').text(EDS_Admin.status_on  || 'Active in Elementor');
    } else {
      $card.removeClass('is-enabled');
      $status.removeClass('is-on').addClass('is-off');
      $status.find('span:last-child').text(EDS_Admin.status_off || 'Hidden from Elementor');
    }
  });

  // ── Init ─────────────────────────────────────────────────────────────────────

  $(function () {
    // Add variable row.
    $('#eds-add-custom').on('click', addCustomRow);

    // Delete variable row (delegated).
    $(document).on('click', '.eds-delete-row', deleteCustomRow);

    // Bootstrap empty state.
    syncEmptyState();

    // Dismiss save notice.
    initSaveNotice();

    // Tab tracking.
    initFormTabTracking();

    // ── Enter key in last value input → add new row ─────────────────────────
    $(document).on('keydown', '#eds-custom-tbody input', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        var $allRows = $('#eds-custom-tbody .eds-custom-row');
        var $thisRow = $(this).closest('tr');
        if (
          $thisRow.is($allRows.last()) &&
          $(this).is('input[name="eds_custom_value[]"]')
        ) {
          addCustomRow();
        }
      }
    });

    // ── Ctrl/Cmd + S → submit form ──────────────────────────────────────────
    $(document).on('keydown', function (e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        var $form = $('#eds-settings-form');
        if ($form.length) {
          e.preventDefault();
          $form.trigger('submit');
        }
      }
    });
  });

}(jQuery));
