export const lightColors = {
  primary: '#FF5722',
  secondary: '#FF7043',
  background: '#F5F5F5',
  surface: '#FFFFFF',
  textPrimary: '#212121',
  textSecondary: '#757575',
  success: '#4CAF50',
  error: '#F44336',
  warning: '#FF9800',
  info: '#2196F3',
  
  // Additional Material Design colors
  onPrimary: '#FFFFFF',
  onSecondary: '#FFFFFF',
  onBackground: '#212121',
  onSurface: '#212121',
  onError: '#FFFFFF',
  onSuccess: '#FFFFFF',
  
  // Border and divider colors
  border: '#E0E0E0',
  divider: '#BDBDBD',
  
  // Shadow colors
  shadow: '#000000',
  
  // Disabled colors
  disabled: '#BDBDBD',
  disabledText: '#9E9E9E',
};

export const darkColors = {
  primary: '#FF7043',
  secondary: '#FF8A65',
  background: '#121212',
  surface: '#1E1E1E',
  textPrimary: '#E0E0E0',
  textSecondary: '#BDBDBD',
  success: '#66BB6A',
  error: '#EF5350',
  warning: '#FFB74D',
  info: '#64B5F6',
  
  // Additional Material Design colors
  onPrimary: '#000000',
  onSecondary: '#000000',
  onBackground: '#E0E0E0',
  onSurface: '#E0E0E0',
  onError: '#000000',
  onSuccess: '#000000',
  
  // Border and divider colors
  border: '#424242',
  divider: '#616161',
  
  // Shadow colors
  shadow: '#000000',
  
  // Disabled colors
  disabled: '#616161',
  disabledText: '#757575',
};

export type ColorScheme = 'light' | 'dark';

export const getColors = (scheme: ColorScheme) => {
  return scheme === 'light' ? lightColors : darkColors;
};
