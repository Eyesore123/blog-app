import React, { Component, ReactNode } from 'react';
import Error from './Error';

interface ErrorBoundaryProps {
  children: ReactNode;
}

interface ErrorBoundaryState {
  hasError: boolean;
  errorStatus?: number;
  errorMessage?: string;
}

export class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
  state: ErrorBoundaryState = {
    hasError: false,
    errorStatus: undefined,
    errorMessage: undefined,
  };

  static getDerivedStateFromError(error: any) {
    let status: number | undefined;
    let message: string | undefined;

    // Handle Inertia errors that have a response (network/visit errors)
    if (error?.response) {
      status = error.response.status;
      message = error.response.statusText || error.message;
    } else if (error?.status) {
      // Custom thrown error with status
      status = error.status;
      message = error.message;
    } else {
      // Fallback: unknown error
      status = undefined;
      message = error?.message || 'An unexpected error occurred.';
    }

    return {
      hasError: true,
      errorStatus: status,
      errorMessage: message,
    };
  }

  componentDidCatch(error: any, errorInfo: any) {
    console.error('Caught by ErrorBoundary:', error, errorInfo);
  }

  render() {
    const { hasError, errorStatus, errorMessage } = this.state;

    if (hasError) {
      return <Error status={errorStatus ?? 500} message={errorMessage} />;
    }

    return this.props.children;
  }
}
