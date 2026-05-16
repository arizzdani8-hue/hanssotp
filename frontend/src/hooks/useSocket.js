import { useEffect } from 'react';
import { getSocket } from '../services/socket';

export function useSocketEvent(event, handler) {
  useEffect(() => {
    const socket = getSocket();
    if (!socket) return;
    socket.on(event, handler);
    return () => socket.off(event, handler);
  }, [event, handler]);
}

export function useOrderSocket(orderId) {
  useEffect(() => {
    const socket = getSocket();
    if (!socket || !orderId) return;
    socket.emit('subscribe:order', orderId);
    return () => socket.emit('unsubscribe:order', orderId);
  }, [orderId]);
}
