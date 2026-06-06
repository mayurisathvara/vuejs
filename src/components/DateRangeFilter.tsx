import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Modal, ScrollView } from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import DatePicker from 'react-native-date-picker';
import { useTheme } from '../contexts/ThemeContext';

interface DateRangeFilterProps {
  onApply: (startDate: Date, endDate: Date, label: string) => void;
  selectedLabel?: string;
}

const DateRangeFilter: React.FC<DateRangeFilterProps> = ({ onApply, selectedLabel = 'Today' }) => {
  const { theme } = useTheme();
  const isDark = theme.colorScheme === 'dark';
  const [modalVisible, setModalVisible] = useState(false);
  const [showStartPicker, setShowStartPicker] = useState(false);
  const [showEndPicker, setShowEndPicker] = useState(false);
  const [startDate, setStartDate] = useState(new Date());
  const [endDate, setEndDate] = useState(new Date());
  const [currentLabel, setCurrentLabel] = useState(selectedLabel);

  const presets = [
    {
      label: 'Today',
      getValue: () => {
        const today = new Date();
        return { start: today, end: today };
      },
    },
    {
      label: 'Yesterday',
      getValue: () => {
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 1);
        return { start: yesterday, end: yesterday };
      },
    },
    {
      label: 'Last 7 Days',
      getValue: () => {
        const end = new Date();
        const start = new Date();
        start.setDate(start.getDate() - 7);
        return { start, end };
      },
    },
    {
      label: 'Last 30 Days',
      getValue: () => {
        const end = new Date();
        const start = new Date();
        start.setDate(start.getDate() - 30);
        return { start, end };
      },
    },
    {
      label: 'This Month',
      getValue: () => {
        const now = new Date();
        const start = new Date(now.getFullYear(), now.getMonth(), 1);
        const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        return { start, end };
      },
    },
  ];

  const handlePresetSelect = (preset: typeof presets[0]) => {
    const { start, end } = preset.getValue();
    setCurrentLabel(preset.label);
    onApply(start, end, preset.label);
    setModalVisible(false);
  };

  const handleCustomRange = () => {
    setShowStartPicker(true);
  };

  const handleStartDateConfirm = (date: Date) => {
    setStartDate(date);
    setShowStartPicker(false);
    setShowEndPicker(true);
  };

  const handleEndDateConfirm = (date: Date) => {
    setEndDate(date);
    setShowEndPicker(false);
    
    const label = `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
    setCurrentLabel(label);
    onApply(startDate, date, label);
    setModalVisible(false);
  };

  const formatDate = (date: Date) => {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  };

  return (
    <View style={styles.container}>
      <TouchableOpacity
        style={[styles.filterButton, { 
          backgroundColor: isDark ? theme.colors.surface : '#fff' 
        }]}
        onPress={() => setModalVisible(true)}
        activeOpacity={0.7}
      >
        <MaterialCommunityIcons name="calendar-range" size={20} color="#FF7A3D" />
        <Text style={[styles.filterButtonText, { 
          color: theme.colors.textPrimary 
        }]}>{currentLabel}</Text>
        <MaterialCommunityIcons name="chevron-down" size={20} color={theme.colors.textSecondary} />
      </TouchableOpacity>

      <Modal
        visible={modalVisible}
        transparent
        animationType="fade"
        onRequestClose={() => setModalVisible(false)}
      >
        <TouchableOpacity
          style={styles.modalOverlay}
          activeOpacity={1}
          onPress={() => setModalVisible(false)}
        >
          <View style={[styles.modalContent, { 
            backgroundColor: isDark ? '#1E1E1E' : '#fff' 
          }]} onStartShouldSetResponder={() => true}>
            <View style={[styles.modalHeader, { 
              borderBottomColor: isDark ? '#333' : '#F0F0F0' 
            }]}>
              <Text style={[styles.modalTitle, { 
                color: theme.colors.textPrimary 
              }]}>Select Date Range</Text>
              <TouchableOpacity onPress={() => setModalVisible(false)}>
                <MaterialCommunityIcons name="close" size={24} color={theme.colors.textSecondary} />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.presetsContainer}>
              {presets.map((preset) => (
                <TouchableOpacity
                  key={preset.label}
                  style={[
                    styles.presetButton,
                    { backgroundColor: isDark ? '#2A2A2A' : '#F9F9F9' },
                    currentLabel === preset.label && styles.presetButtonActive,
                  ]}
                  onPress={() => handlePresetSelect(preset)}
                  activeOpacity={0.7}
                >
                  <MaterialCommunityIcons
                    name="calendar-check"
                    size={20}
                    color={currentLabel === preset.label ? '#FF7A3D' : theme.colors.textSecondary}
                  />
                  <Text
                    style={[
                      styles.presetText,
                      { color: isDark ? theme.colors.textSecondary : '#666' },
                      currentLabel === preset.label && styles.presetTextActive,
                    ]}
                  >
                    {preset.label}
                  </Text>
                </TouchableOpacity>
              ))}

              <TouchableOpacity
                style={[styles.customButton, { 
                  backgroundColor: isDark ? '#1E3A5F' : '#E3F2FD' 
                }]}
                onPress={handleCustomRange}
                activeOpacity={0.7}
              >
                <MaterialCommunityIcons name="calendar-edit" size={20} color={isDark ? '#64B5F6' : '#2196F3'} />
                <Text style={[styles.customText, { 
                  color: isDark ? '#64B5F6' : '#2196F3' 
                }]}>Custom Range</Text>
                <MaterialCommunityIcons name="chevron-right" size={20} color={theme.colors.textSecondary} />
              </TouchableOpacity>
            </ScrollView>
          </View>
        </TouchableOpacity>
      </Modal>

      {/* Start Date Picker */}
      <DatePicker
        modal
        open={showStartPicker}
        date={startDate}
        mode="date"
        onConfirm={handleStartDateConfirm}
        onCancel={() => setShowStartPicker(false)}
        title="Select Start Date"
        maximumDate={new Date()}
      />

      {/* End Date Picker */}
      <DatePicker
        modal
        open={showEndPicker}
        date={endDate}
        mode="date"
        onConfirm={handleEndDateConfirm}
        onCancel={() => setShowEndPicker(false)}
        title="Select End Date"
        minimumDate={startDate}
        maximumDate={new Date()}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginBottom: 16,
  },
  filterButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderRadius: 12,
    gap: 8,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
  },
  filterButtonText: {
    fontSize: 14,
    fontWeight: '600',
    flex: 1,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    borderRadius: 16,
    width: '85%',
    maxWidth: 400,
    maxHeight: '70%',
    elevation: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '700',
  },
  presetsContainer: {
    padding: 16,
  },
  presetButton: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderRadius: 12,
    marginBottom: 8,
    gap: 12,
  },
  presetButtonActive: {
    backgroundColor: '#FFF3E0',
    borderWidth: 1.5,
    borderColor: '#FF7A3D',
  },
  presetText: {
    fontSize: 15,
    fontWeight: '600',
    flex: 1,
  },
  presetTextActive: {
    color: '#FF7A3D',
  },
  customButton: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderRadius: 12,
    marginTop: 8,
    gap: 12,
  },
  customText: {
    fontSize: 15,
    fontWeight: '600',
    flex: 1,
  },
});

export default DateRangeFilter;
