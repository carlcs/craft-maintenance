(function($) {

Craft.MaintenanceModal = Garnish.Base.extend(
{
  announcement: null,
  message: null,
  meta: null,
  maintenanceAccess: null,

  init: function(announcement, message, meta, maintenanceAccess)
  {
    this.announcement = announcement;
    this.message = message;
    this.meta = meta;
    this.maintenanceAccess = maintenanceAccess;

    this.showMaintenanceModal();
  },

  showMaintenanceModal: function(ev)
  {
    var $modal = $('<div id="maintenanceOverlay" class="modal alert fitted">').appendTo(Garnish.$bod);
    var $body = $('<div class="body"></div>').appendTo($modal);

    var $header = $('<div class="maintenanceOverlay-header"></div>').appendTo($body);
    var $message = $('<div class="maintenanceOverlay-message">'+this.message+'</div>').appendTo($body);
    var $titel = $('<h2 class="maintenanceOverlay-title">' + Craft.t('Maintenance in progress.') + '</h2>').appendTo($header);
    var $meta = $('<span class="maintenanceOverlay-meta">'+this.meta+'</span>').appendTo($header);

    this.assetModal = new Garnish.Modal($modal, {
      autoShow: false,
      closeOtherModals: false,
      hideOnEsc: this.maintenanceAccess,
      hideOnShadeClick: this.maintenanceAccess,
      shadeClass: 'modal-shade dark',
    });

    this.assetModal.show();
  }
});

Craft.MaintenanceCountdown = Garnish.Base.extend(
{
  counter: null,
  wrapper: null,
  endTimeClient: null,

  timer: null,

  init: function(id, timeRemaining)
  {
    this.counter = document.getElementById(id);
    this.wrapper = this.counter.parentNode;
    this.endTimeClient = new Date(Date.now() + (timeRemaining * 1000));

    this.wrapper.classList.remove('hidden');

    this.initializeClock();
  },

  initializeClock: function()
  {
    this.updateClock();
    this.timer = setInterval(this.updateClock.bind(this), 1000);
  },

  updateClock: function()
  {
    var t = this.getTimeRemaining(this.endTimeClient);

    if (t.total <= 0) {
      clearInterval(this.timer);
      this.wrapper.innerHTML = Craft.t('Maintenance in progress.');
    } else {
      this.counter.innerHTML = t.minutes+1;
    }
  },

  getTimeRemaining: function(endDate)
  {
    var t = endDate.getTime() - Date.now();

    return {
      'total': t,
      'seconds': Math.floor((t/1000) % 60),
      'minutes': Math.floor((t/1000/60)),
    };
  },
});

})(jQuery);
