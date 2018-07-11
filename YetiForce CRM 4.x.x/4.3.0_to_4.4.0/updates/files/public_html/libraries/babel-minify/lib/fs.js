"use strict";

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function step(key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } } function _next(value) { step("next", value); } function _throw(err) { step("throw", err); } _next(); }); }; }

const fs = require("fs");

const path = require("path");

const readdir = require("fs-readdir-recursive");

const promisify = require("util.promisify");

const mkdirp = promisify(require("mkdirp"));

const minify = require("./");

const EXTENSIONS = [".js", ".mjs"];
const readFileAsync = promisify(fs.readFile);
const writeFileAsync = promisify(fs.writeFile);
const lstat = promisify(fs.lstat);

class MinifyFileError extends Error {
  constructor(message, {
    file
  }) {
    super(message);
    this.file = file;
  }

} // set defaults


const readFile = file => readFileAsync(file, {
  encoding: "utf-8"
});

const writeFile = (file, data) => writeFileAsync(file, data, {
  encoding: "utf-8"
});

function isJsFile(file) {
  return EXTENSIONS.some(ext => path.extname(file) === ext);
}

function isDir(_x) {
  return _isDir.apply(this, arguments);
}

function _isDir() {
  _isDir = _asyncToGenerator(function* (p) {
    try {
      return (yield lstat(p)).isDirectory();
    } catch (e) {
      return false;
    }
  });
  return _isDir.apply(this, arguments);
}

function isFile(_x2) {
  return _isFile.apply(this, arguments);
} // the async keyword simply exists to denote we are returning a promise
// even though we don't use await inside it


function _isFile() {
  _isFile = _asyncToGenerator(function* (p) {
    try {
      return (yield lstat(p)).isFile();
    } catch (e) {
      return false;
    }
  });
  return _isFile.apply(this, arguments);
}

function readStdin() {
  return _readStdin.apply(this, arguments);
}

function _readStdin() {
  _readStdin = _asyncToGenerator(function* () {
    let code = "";
    const stdin = process.stdin;
    return new Promise(resolve => {
      stdin.setEncoding("utf8");
      stdin.on("readable", () => {
        const chunk = process.stdin.read();
        if (chunk !== null) code += chunk;
      });
      stdin.on("end", () => {
        resolve(code);
      });
    });
  });
  return _readStdin.apply(this, arguments);
}

function handleStdin(_x3, _x4) {
  return _handleStdin.apply(this, arguments);
}

function _handleStdin() {
  _handleStdin = _asyncToGenerator(function* (outputFilename, options) {
    const _minify = minify((yield readStdin()), options),
          code = _minify.code;

    if (outputFilename) {
      yield writeFile(outputFilename, code);
    } else {
      process.stdout.write(code);
    }
  });
  return _handleStdin.apply(this, arguments);
}

function handleFile(_x5, _x6, _x7) {
  return _handleFile.apply(this, arguments);
}

function _handleFile() {
  _handleFile = _asyncToGenerator(function* (filename, outputFilename, options) {
    const _minify2 = minify((yield readFile(filename)), options),
          code = _minify2.code;

    if (outputFilename) {
      yield writeFile(outputFilename, code);
    } else {
      process.stdout.write(code);
    }
  });
  return _handleFile.apply(this, arguments);
}

function handleFiles(_x8, _x9, _x10) {
  return _handleFiles.apply(this, arguments);
}

function _handleFiles() {
  _handleFiles = _asyncToGenerator(function* (files, outputDir, options) {
    if (!outputDir) {
      throw new TypeError(`outputDir is falsy. Got "${outputDir}"`);
    }

    return Promise.all(files.map(file => {
      const outputFilename = path.join(outputDir, path.basename(file));
      return mkdirp(path.dirname(outputFilename)).then(() => handleFile(file, outputFilename, options)).catch(e => Promise.reject(new MinifyFileError(e.message, {
        file
      })));
    }));
  });
  return _handleFiles.apply(this, arguments);
}

function handleDir(_x11, _x12, _x13) {
  return _handleDir.apply(this, arguments);
}

function _handleDir() {
  _handleDir = _asyncToGenerator(function* (dir, outputDir, options) {
    if (!outputDir) {
      throw new TypeError(`outputDir is falsy`);
    } // relative paths


    const files = readdir(dir);
    return Promise.all(files.filter(file => isJsFile(file)).map(file => {
      const outputFilename = path.join(outputDir, file);
      const inputFilename = path.join(dir, file);
      return mkdirp(path.dirname(outputFilename)).then(() => handleFile(inputFilename, outputFilename, options)).catch(e => Promise.reject(new MinifyFileError(e.message, {
        file: inputFilename
      })));
    }));
  });
  return _handleDir.apply(this, arguments);
}

function handleArgs(_x14, _x15, _x16) {
  return _handleArgs.apply(this, arguments);
}

function _handleArgs() {
  _handleArgs = _asyncToGenerator(function* (args, outputDir, options) {
    const files = [];
    const dirs = [];

    if (!Array.isArray(args)) {
      throw new TypeError(`Expected Array. Got ${JSON.stringify(args)}`);
    }

    var _iteratorNormalCompletion = true;
    var _didIteratorError = false;
    var _iteratorError = undefined;

    try {
      for (var _iterator = args[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
        const arg = _step.value;

        if (yield isFile(arg)) {
          files.push(arg);
        } else if (yield isDir(arg)) {
          dirs.push(arg);
        } else {
          throw new TypeError(`Input "${arg}" is neither a file nor a directory.`);
        }
      }
    } catch (err) {
      _didIteratorError = true;
      _iteratorError = err;
    } finally {
      try {
        if (!_iteratorNormalCompletion && _iterator.return != null) {
          _iterator.return();
        }
      } finally {
        if (_didIteratorError) {
          throw _iteratorError;
        }
      }
    }

    return Promise.all([handleFiles(files, outputDir, options), ...dirs.map(dir => handleDir(dir, outputDir, options))]);
  });
  return _handleArgs.apply(this, arguments);
}

module.exports = {
  handleFile,
  handleStdin,
  handleFiles,
  handleDir,
  handleArgs,
  isFile,
  isDir,
  isJsFile,
  readFile,
  writeFile
};