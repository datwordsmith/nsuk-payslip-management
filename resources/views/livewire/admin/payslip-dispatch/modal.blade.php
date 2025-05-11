<!-- Dispatch Confirmation Modal -->
<div class="modal fade" id="confirmDispatchModal" tabindex="-1" aria-labelledby="confirmDispatchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmDispatchModalLabel">Confirm Dispatch</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to dispatch payslips for:
          <strong>
              {{ $month ? date('F', mktime(0, 0, 0, $month, 1)) : '---' }}
              {{ $year ?: '---' }}
          </strong>
          ?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary"
                  wire:click="dispatchPayslips"
                  data-bs-dismiss="modal">
              Yes, Dispatch
          </button>
        </div>
      </div>
    </div>
</div>

  <!-- Resend Confirmation Modal -->
<div class="modal fade" id="confirmResendModal" tabindex="-1" aria-labelledby="confirmResendModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmResendModalLabel">Confirm Resend</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to resend this payslip?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" wire:click="confirmResend({{ $resendId }})" data-bs-dismiss="modal">Yes, Resend</button>
        </div>
      </div>
    </div>
</div>
