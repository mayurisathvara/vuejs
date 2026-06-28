import { StyleSheet } from 'react-native';
import { spacing, borderRadius, shadows } from './spacing';
import { typography } from './typography';

export const createComponentStyles = (colors: any) => StyleSheet.create({
  // Button styles
  primaryButton: {
    backgroundColor: colors.primary,
    borderRadius: borderRadius.lg,
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 56,
    ...shadows.sm,
  },
  
  secondaryButton: {
    backgroundColor: 'transparent',
    borderWidth: 1.5,
    borderColor: colors.primary,
    borderRadius: borderRadius.lg,
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.lg,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 56,
  },
  
  textButton: {
    backgroundColor: 'transparent',
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.md,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 40,
  },
  
  buttonText: {
    ...typography.button,
    color: colors.onPrimary,
    fontWeight: '600',
  },
  
  secondaryButtonText: {
    ...typography.button,
    color: colors.primary,
    fontWeight: '600',
  },
  
  textButtonText: {
    ...typography.button,
    color: colors.primary,
    fontWeight: '500',
  },
  
  // Card styles
  card: {
    backgroundColor: colors.surface,
    borderRadius: borderRadius.lg,
    padding: spacing.md,
    ...shadows.sm,
    borderWidth: 1,
    borderColor: colors.border,
  },
  
  elevatedCard: {
    backgroundColor: colors.surface,
    borderRadius: borderRadius.lg,
    padding: spacing.md,
    ...shadows.md,
  },
  
  // Input styles
  inputContainer: {
    marginBottom: spacing.md,
  },
  
  inputLabel: {
    ...typography.subtitle2,
    color: colors.textPrimary,
    marginBottom: spacing.sm,
    fontWeight: '500',
  },
  
  inputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1.5,
    borderColor: colors.border,
    borderRadius: borderRadius.lg,
    backgroundColor: colors.surface,
    paddingHorizontal: spacing.md,
    minHeight: 56,
  },
  
  inputWrapperFocused: {
    borderColor: colors.primary,
    borderWidth: 2,
  },
  
  input: {
    flex: 1,
    fontSize: 16,
    color: colors.textPrimary,
    paddingVertical: 0,
  },
  
  inputError: {
    borderColor: colors.error,
    borderWidth: 2,
  },
  
  errorText: {
    ...typography.caption,
    color: colors.error,
    marginTop: spacing.xs,
  },
  
  // Loading styles
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.background,
  },
  
  shimmerContainer: {
    backgroundColor: colors.surface,
    borderRadius: borderRadius.lg,
    padding: spacing.md,
    marginBottom: spacing.md,
  },
  
  // Screen styles
  screen: {
    flex: 1,
    backgroundColor: colors.background,
  },
  
  screenContent: {
    flex: 1,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.lg,
  },
  
  // Header styles
  header: {
    alignItems: 'center',
    marginBottom: spacing.xxl,
    paddingTop: spacing.lg,
  },
  
  headerTitle: {
    ...typography.h3,
    color: colors.textPrimary,
    textAlign: 'center',
    marginBottom: spacing.sm,
  },
  
  headerSubtitle: {
    ...typography.body1,
    color: colors.textSecondary,
    textAlign: 'center',
  },
  
  // List styles
  listItem: {
    backgroundColor: colors.surface,
    borderRadius: borderRadius.lg,
    padding: spacing.md,
    marginBottom: spacing.sm,
    flexDirection: 'row',
    alignItems: 'center',
    ...shadows.xs,
  },
  
  listItemContent: {
    flex: 1,
    marginLeft: spacing.md,
  },
  
  listItemTitle: {
    ...typography.subtitle1,
    color: colors.textPrimary,
    marginBottom: spacing.xs,
  },
  
  listItemSubtitle: {
    ...typography.body2,
    color: colors.textSecondary,
  },
  
  // Divider
  divider: {
    height: 1,
    backgroundColor: colors.divider,
    marginVertical: spacing.md,
  },
  
  // Badge styles
  badge: {
    backgroundColor: colors.primary,
    borderRadius: borderRadius.xl,
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    alignItems: 'center',
    justifyContent: 'center',
    minWidth: 20,
    minHeight: 20,
  },
  
  badgeText: {
    ...typography.caption,
    color: colors.onPrimary,
    fontWeight: '600',
  },
  
  // Status styles
  successBadge: {
    backgroundColor: colors.success,
  },
  
  errorBadge: {
    backgroundColor: colors.error,
  },
  
  warningBadge: {
    backgroundColor: colors.warning,
  },
  
  infoBadge: {
    backgroundColor: colors.info,
  },
});
