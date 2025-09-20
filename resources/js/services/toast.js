// Toast notification service
export const showToast = (message, type = 'success', title = 'Success') => {
  if (typeof window !== 'undefined' && window.$ && window.$.notify) {
    const notification = window.$.notify({
      icon: getIcon(type),
      title: title,
      message: message,
    }, {
      type: type,
      placement: {
        from: "bottom",
        align: "right"
      },
      time: 0,  // Disable auto-close initially
      delay: 0,
      allow_dismiss: true,
      newest_on_top: true,
      mouse_over: false,
      showProgressbar: false,
      spacing: 10,
      timer: 0,
      url: '',
      animate: {
        enter: 'animated fadeInDown',
        exit: 'animated fadeOutUp'
      }
    });

    // Manual auto-close after 3 seconds
    setTimeout(() => {
      if (notification && notification.close) {
        notification.close();
      }
    }, 3000);
  }
}

const getIcon = (type) => {
  const icons = {
    success: 'fa fa-check',
    error: 'fa fa-times',
    warning: 'fa fa-exclamation',
    info: 'fa fa-info',
    primary: 'fa fa-bell',
    secondary: 'fa fa-bell'
  }
  return icons[type] || icons.success
}

// Convenience methods
export const showSuccess = (message, title = 'Success') => {
  showToast(message, 'success', title)
}

export const showError = (message, title = 'Error') => {
  showToast(message, 'danger', title)
}

export const showWarning = (message, title = 'Warning') => {
  showToast(message, 'warning', title)
}

export const showInfo = (message, title = 'Info') => {
  showToast(message, 'info', title)
}
