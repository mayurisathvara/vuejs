import { useEffect } from 'react';
import { BackHandler } from 'react-native';

interface UseBackHandlerOptions {
  enabled: boolean;
  onBackPress?: () => boolean | null | undefined;
}

export const useBackHandler = ({ enabled, onBackPress }: UseBackHandlerOptions) => {
  useEffect(() => {
    if (!enabled) return;

    const backAction = () => {
      if (onBackPress) {
        return onBackPress();
      }
      // Default behavior: prevent going back
      return true;
    };

    const backHandler = BackHandler.addEventListener('hardwareBackPress', backAction);

    return () => backHandler.remove();
  }, [enabled, onBackPress]);
};

export default useBackHandler;
