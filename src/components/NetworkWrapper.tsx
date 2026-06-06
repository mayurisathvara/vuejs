import React from 'react';
import { useNetwork } from '../contexts/NetworkContext';
import NetworkErrorPage from './NetworkErrorPage';

interface NetworkWrapperProps {
  children: React.ReactNode;
  // Allow specific screens to work offline
  allowOffline?: boolean;
}

const NetworkWrapper: React.FC<NetworkWrapperProps> = ({ children, allowOffline = false }) => {
  const { isConnected, isInternetReachable } = useNetwork();

  // Show network error only if offline mode is not allowed AND no connection
  const shouldShowError = !allowOffline && (!isConnected || isInternetReachable === false);

  if (shouldShowError) {
    return <NetworkErrorPage />;
  }

  return <>{children}</>;
};

export default NetworkWrapper;
