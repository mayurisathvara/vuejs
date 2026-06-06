import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import NetInfo, { NetInfoState } from '@react-native-community/netinfo';

interface NetworkContextType {
  isConnected: boolean;
  isInternetReachable: boolean | null;
  networkType: string | null;
  isNetworkLoading: boolean;
  retryConnection: () => void;
}

const NetworkContext = createContext<NetworkContextType | undefined>(undefined);

interface NetworkProviderProps {
  children: ReactNode;
}

export const NetworkProvider: React.FC<NetworkProviderProps> = ({ children }) => {
  const [isConnected, setIsConnected] = useState<boolean>(true);
  const [isInternetReachable, setIsInternetReachable] = useState<boolean | null>(true);
  const [networkType, setNetworkType] = useState<string | null>(null);
  const [isNetworkLoading, setIsNetworkLoading] = useState<boolean>(false);

  const checkConnection = async () => {
    setIsNetworkLoading(true);
    try {
      const state: NetInfoState = await NetInfo.fetch();
      setIsConnected(state.isConnected ?? false);
      setIsInternetReachable(state.isInternetReachable);
      setNetworkType(state.type);
    } catch (error) {
      console.warn('Error checking network connection:', error);
      setIsConnected(false);
      setIsInternetReachable(false);
    } finally {
      setIsNetworkLoading(false);
    }
  };

  const retryConnection = () => {
    checkConnection();
  };

  useEffect(() => {
    // Initial connection check
    checkConnection();

    // Subscribe to network state changes
    const unsubscribe = NetInfo.addEventListener((state: NetInfoState) => {
      setIsConnected(state.isConnected ?? false);
      setIsInternetReachable(state.isInternetReachable);
      setNetworkType(state.type);
    });

    return () => {
      unsubscribe();
    };
  }, []);

  const value: NetworkContextType = {
    isConnected,
    isInternetReachable,
    networkType,
    isNetworkLoading,
    retryConnection,
  };

  return (
    <NetworkContext.Provider value={value}>
      {children}
    </NetworkContext.Provider>
  );
};

export const useNetwork = (): NetworkContextType => {
  const context = useContext(NetworkContext);
  if (context === undefined) {
    throw new Error('useNetwork must be used within a NetworkProvider');
  }
  return context;
};

export default NetworkProvider;
