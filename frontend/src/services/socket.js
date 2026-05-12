import { io } from 'socket.io-client';

const WS_URL = import.meta.env.VITE_WS_URL || '';
let socket = null;

export function connectSocket(token) {
  if (socket?.connected) return socket;
  socket = io(WS_URL, {
    auth: { token },
    transports: ['websocket', 'polling'],
  });
  socket.on('connect', () => console.log('Socket connected'));
  socket.on('disconnect', () => console.log('Socket disconnected'));
  return socket;
}

export function getSocket() {
  return socket;
}

export function disconnectSocket() {
  if (socket) {
    socket.disconnect();
    socket = null;
  }
}
