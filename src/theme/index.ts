import { getColors, ColorScheme } from './colors';
import { typography, fontFamily } from './typography';
import { spacing, borderRadius, elevation, shadows } from './spacing';
import { createComponentStyles } from './components';

export interface Theme {
  colors: ReturnType<typeof getColors>;
  typography: typeof typography;
  fontFamily: typeof fontFamily;
  spacing: typeof spacing;
  borderRadius: typeof borderRadius;
  elevation: typeof elevation;
  shadows: typeof shadows;
  components: ReturnType<typeof createComponentStyles>;
  // track the selected color scheme
  colorScheme?: ColorScheme;
}

export const createTheme = (colorScheme: ColorScheme): Theme => {
  const colors = getColors(colorScheme);
  
  return {
    colors,
    colorScheme,
    typography,
    fontFamily,
    spacing,
    borderRadius,
    elevation,
    shadows,
    components: createComponentStyles(colors),
  };
};

export { getColors, lightColors, darkColors } from './colors';
export type { ColorScheme } from './colors';
export { typography, fontFamily } from './typography';
export { spacing, borderRadius, elevation, shadows } from './spacing';
export { createComponentStyles } from './components';
