/* global document */
import Injector from 'lib/Injector';
import { combineReducers } from 'redux';
import ElevationReducer from '../state/ElevationReducer';
import ElevationApp from '../components/ElevationApp';

document.addEventListener('DOMContentLoaded', () => {
  // Register Reducer
  Injector.reducer.register(
    'FireSphere', // sort of module namespace
    combineReducers({
      Elevation: ElevationReducer, // store reference index
    })
  );

  // Register React Component
  Injector.component.register('ElevationApp', ElevationApp);
});
