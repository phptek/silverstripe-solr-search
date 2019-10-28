/* global window */
import deepFreeze from 'deep-freeze-strict';
import { CHANGE, CLEAN } from './ElevationActionTypes';

function reducer(state = {}, action) {
  switch (action.type) {
    case CHANGE:
      return deepFreeze({...state, ...action.payload});
    case CLEAN:
      return deepFreeze({...state, ...{[action.payload.name]: ''}});
    default:
      return deepFreeze(state);
  }
}

export default reducer;
