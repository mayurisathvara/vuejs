// Toast notification service
export const showToast = (message, type = 'success', title = 'Success') => {
  if (typeof window !== 'undefined' && window.$) {
    try {
      // Check if bootstrap-notify is available
      if (window.$.notify) {
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
      } else {
        // Fallback: Use browser's built-in notification if available
        if ('Notification' in window && Notification.permission === 'granted') {
          new Notification(title, {
            body: message,
            icon: '/assets/img/favicon.ico'
          });
        } else {
          // Final fallback: Use alert
          alert(`${title}: ${message}`);
        }
      }
    } catch (error) {
      console.error('Toast notification error:', error);
      // Fallback to browser alert if toast fails
      alert(`${title}: ${message}`);
    }
  } else {
    console.warn('jQuery not available. Using alert fallback.');
    alert(`${title}: ${message}`);
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
