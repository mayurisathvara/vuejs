declare module 'react-native-push-notification' {
  export interface PushNotificationOptions {
    channelId?: string;
    ticker?: string;
    showWhen?: boolean;
    autoCancel?: boolean;
    largeIcon?: string;
    largeIconUrl?: string;
    smallIcon?: string;
    bigText?: string;
    subText?: string;
    bigPictureUrl?: string;
    bigLargeIcon?: string;
    bigLargeIconUrl?: string;
    color?: string;
    vibrate?: boolean;
    vibration?: number;
    tag?: string;
    group?: string;
    groupSummary?: boolean;
    ongoing?: boolean;
    priority?: string;
    visibility?: string;
    ignoreInForeground?: boolean;
    shortcutId?: string;
    onlyAlertOnce?: boolean;
    when?: number;
    usesChronometer?: boolean;
    timeoutAfter?: number;
    messageId?: string;
    actions?: string[];
    invokeApp?: boolean;
    userInfo?: any;
    playSound?: boolean;
    soundName?: string;
    number?: number;
    repeatType?: string;
    repeatTime?: number;
    id?: number | string;
    title?: string;
    message: string;
    picture?: string;
    pictureUrl?: string;
    data?: any;
  }

  export interface ChannelOptions {
    channelId: string;
    channelName: string;
    channelDescription?: string;
    playSound?: boolean;
    soundName?: string;
    importance?: number;
    vibrate?: boolean;
  }

  export default class PushNotification {
    static configure(options: any): void;
    static localNotification(options: PushNotificationOptions): void;
    static localNotificationSchedule(options: PushNotificationOptions): void;
    static cancelAllLocalNotifications(): void;
    static createChannel(channel: ChannelOptions, callback?: (created: boolean) => void): void;
    static channelExists(channelId: string, callback: (exists: boolean) => void): void;
    static getChannels(callback: (channels: string[]) => void): void;
  }
}

declare module 'react-native-push-notification/index' {
  import PushNotification from 'react-native-push-notification';
  export default PushNotification;
}
