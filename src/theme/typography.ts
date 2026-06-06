import { Platform, PixelRatio, Dimensions } from 'react-native';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

// Base font size for scaling
const scale = SCREEN_WIDTH / 320;

export const normalize = (size: number): number => {
  const newSize = size * scale;
  if (Platform.OS === 'ios') {
    return Math.round(PixelRatio.roundToNearestPixel(newSize));
  } else {
    return Math.round(PixelRatio.roundToNearestPixel(newSize)) - 2;
  }
};

export const typography = {
  // Headings
  h1: {
    fontSize: normalize(32),
    fontWeight: '600' as const,
    lineHeight: normalize(40),
    letterSpacing: -0.5,
  },
  h2: {
    fontSize: normalize(28),
    fontWeight: '600' as const,
    lineHeight: normalize(36),
    letterSpacing: -0.25,
  },
  h3: {
    fontSize: normalize(24),
    fontWeight: '600' as const,
    lineHeight: normalize(32),
    letterSpacing: 0,
  },
  h4: {
    fontSize: normalize(20),
    fontWeight: '600' as const,
    lineHeight: normalize(28),
    letterSpacing: 0.15,
  },
  h5: {
    fontSize: normalize(18),
    fontWeight: '600' as const,
    lineHeight: normalize(24),
    letterSpacing: 0.15,
  },
  h6: {
    fontSize: normalize(16),
    fontWeight: '600' as const,
    lineHeight: normalize(24),
    letterSpacing: 0.15,
  },
  
  // Body text
  body1: {
    fontSize: normalize(16),
    fontWeight: '400' as const,
    lineHeight: normalize(24),
    letterSpacing: 0.5,
  },
  body2: {
    fontSize: normalize(14),
    fontWeight: '400' as const,
    lineHeight: normalize(20),
    letterSpacing: 0.25,
  },
  
  // Labels and captions
  caption: {
    fontSize: normalize(12),
    fontWeight: '400' as const,
    lineHeight: normalize(16),
    letterSpacing: 0.4,
  },
  overline: {
    fontSize: normalize(10),
    fontWeight: '500' as const,
    lineHeight: normalize(16),
    letterSpacing: 1.5,
    textTransform: 'uppercase' as const,
  },
  
  // Button text
  button: {
    fontSize: normalize(14),
    fontWeight: '500' as const,
    lineHeight: normalize(20),
    letterSpacing: 1.25,
    textTransform: 'uppercase' as const,
  },
  
  // Special text styles
  subtitle1: {
    fontSize: normalize(16),
    fontWeight: '500' as const,
    lineHeight: normalize(24),
    letterSpacing: 0.15,
  },
  subtitle2: {
    fontSize: normalize(14),
    fontWeight: '500' as const,
    lineHeight: normalize(20),
    letterSpacing: 0.1,
  },
};

export const fontFamily = {
  regular: Platform.OS === 'ios' ? 'Poppins-Regular' : 'Poppins-Regular',
  medium: Platform.OS === 'ios' ? 'Poppins-Medium' : 'Poppins-Medium',
  semiBold: Platform.OS === 'ios' ? 'Poppins-SemiBold' : 'Poppins-SemiBold',
  bold: Platform.OS === 'ios' ? 'Poppins-Bold' : 'Poppins-Bold',
};
