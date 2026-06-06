import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { useColorScheme } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Theme, createTheme, ColorScheme } from '../theme';
import { Provider as PaperProvider, MD3LightTheme, MD3DarkTheme } from 'react-native-paper';

interface ThemeContextType {
  theme: Theme;
  colorScheme: ColorScheme;
  isDark: boolean;
  toggleTheme: () => void;
  setTheme: (scheme: ColorScheme) => void;
}

const ThemeContext = createContext<ThemeContextType | undefined>(undefined);

const THEME_STORAGE_KEY = '@theme_preference';

interface ThemeProviderProps {
  children: ReactNode;
}

export const ThemeProvider: React.FC<ThemeProviderProps> = ({ children }) => {
  const systemColorScheme = useColorScheme();
  const [colorScheme, setColorScheme] = useState<ColorScheme>('light');
  const [isInitialized, setIsInitialized] = useState(false);

  // Initialize theme from storage or system preference
  useEffect(() => {
    const initializeTheme = async () => {
      try {
        const storedTheme = await AsyncStorage.getItem(THEME_STORAGE_KEY);
        if (storedTheme && (storedTheme === 'light' || storedTheme === 'dark')) {
          setColorScheme(storedTheme);
        } else {
          // Use system preference if no stored preference
          setColorScheme(systemColorScheme || 'light');
        }
      } catch (error) {
        console.warn('Failed to load theme preference:', error);
        setColorScheme(systemColorScheme || 'light');
      } finally {
        setIsInitialized(true);
      }
    };

    initializeTheme();
  }, [systemColorScheme]);

  // Save theme preference to storage
  useEffect(() => {
    if (isInitialized) {
      AsyncStorage.setItem(THEME_STORAGE_KEY, colorScheme).catch((error) => {
        console.warn('Failed to save theme preference:', error);
      });
    }
  }, [colorScheme, isInitialized]);

  const toggleTheme = () => {
    setColorScheme((prev: ColorScheme) => (prev === 'light' ? 'dark' : 'light'));
  };

  const setTheme = (scheme: ColorScheme) => {
    setColorScheme(scheme);
  };

  const theme = createTheme(colorScheme);
  const isDark = colorScheme === 'dark';

  const value: ThemeContextType = {
    theme,
    colorScheme,
    isDark,
    toggleTheme,
    setTheme,
  };

  // Don't render until theme is initialized
  if (!isInitialized) {
    return null;
  }

  return (
    <ThemeContext.Provider value={value}>
      <PaperProvider theme={colorScheme === 'light' ? MD3LightTheme : MD3DarkTheme}>
        {children}
      </PaperProvider>
    </ThemeContext.Provider>
  );
};

export const useTheme = (): ThemeContextType => {
  const context = useContext(ThemeContext);
  if (context === undefined) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};

export default ThemeProvider;
